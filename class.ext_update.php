<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Ameos\AmeosFilemanager\Utility\FilemanagerUtility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

class ext_update
{

    /**
     * Main function, returning the HTML content of the module
     * @return string HTML
     */
    public function main()
    {
        if (!GeneralUtility::_GP('do_initialize')) {
            $content = $this->displayWarning();
            $onClick = 'document.location="' . GeneralUtility::linkThisScript(array('do_initialize'=>1)) . '"; return false;';
            $content .= htmlspecialchars($GLOBALS['LANG']->getLL('update_convert_now')) . '
                <br /><br />
                <form action=""><input type="submit" value="' . LocalizationUtility::translate('doInitialize', 'ameos_filemanager') . '" onclick="' . htmlspecialchars($onClick) . '"></form>';
        } else {
            $this->countFolder = 0;
            $this->countAddedFolder = 0;
            
            $updated = $this->initializeDatabase();
            $content .= $updated.'<br/>'.LocalizationUtility::translate('initializeCountFolder', 'ameos_filemanager').' : '.$this->countFolder. ' - ' . $this->countAddedFolder .'<br/>'.LocalizationUtility::translate('adviceInitialize', 'ameos_filemanager');

        }
        return $content;
    }

    /**
     * Checks if extension is loaoed
     * @return boolean true if user have access, otherwise false
     */
    public function access()
    {
        // We cannot update before the extension is installed: required tables are not yet in TCA
        return ExtensionManagementUtility::isLoaded('ameos_filemanager');
    }

    /**
     * Go through the DB and initialize the basic settings to use the extension. 
     * @return string indication about the success or failure of the task
     */
    protected function initializeDatabase()
    {
        $contenu = '';
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $storageRepository = $this->objectManager->get(StorageRepository::class);
        $storages = $storageRepository->findAll();
        $i=0;

        $GLOBALS['TYPO3_DB']->sql_query('DELETE FROM tx_ameosfilemanager_domain_model_folder WHERE identifier = \'\' OR identifier IS NULL');
        $GLOBALS['TYPO3_DB']->sql_query('UPDATE tx_ameosfilemanager_domain_model_folder SET deleted = 1');
        
        foreach ($storages as $storage) {
            $this->currentStorage = $storage;
            $storagePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $storage->getConfiguration()['basePath'];
            $this->setDatabaseForFolder(substr($storagePath, 0,-1), $storagePath, 0, $storage->getUid());
        }
        return LocalizationUtility::translate('initializeSuccess', 'ameos_filemanager');
    }

    /**
     *
     * Display the description of the task
     * @return string
     */
    protected function displayWarning()
    {
        $out = '
            <div style="padding:15px 15px 20px 0;">
                <div class="typo3-message message-warning">
                        <div class="message-header">' . LocalizationUtility::translate('warning', 'ameos_filemanager') . '</div>
                        <div class="message-body">' . LocalizationUtility::translate('warningInitialize', 'ameos_filemanager') . '</div>
                    </div>
                </div>
            </div>';

        return $out;
    }

    /**
     *
     * Parse a folder and add the necessary folder/file into the database
     *
     * @param string $storageFolderPath storage folder path
     * @param Ameos\AmeosFilemanager\Domain\Model\Folder $folder the folder currently in treatment
     * @param int $uidParent his parent's uid
     * @param int $storage storage uid
     */
    protected function setDatabaseForFolder($storageFolderPath, $folder, $uidParent = 0, $storage)
    {
        $files = [];
        $this->countFolder++;
        if ($handle = opendir($folder)) {
            while (($entry = readdir($handle)) !== false) {
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($folder . $entry)) {
                        $identifier = '/' . trim(str_replace($storageFolderPath, '', $folder . $entry), '/') . '/';
                        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                            'tx_ameosfilemanager_domain_model_folder.uid, tx_ameosfilemanager_domain_model_folder.storage',
                            'tx_ameosfilemanager_domain_model_folder',
                            'tx_ameosfilemanager_domain_model_folder.identifier like \''. $identifier . '\'
                                AND (
                                    tx_ameosfilemanager_domain_model_folder.storage = 0
                                    OR tx_ameosfilemanager_domain_model_folder.storage = ' . $storage . '
                                )'
                        );
                        $exist = false;
                        if (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) !== false) {                            
                            $exist = true;
                            $uid = $row['uid'];

                            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_ameosfilemanager_domain_model_folder', 'uid = ' . $uid, [
                                'storage'    => $storage,
                                'deleted'    => 0,
                                'uid_parent' => $uidParent,
                            ]);
                        }

                        if (!$exist) {
                            $this->countAddedFolder ++;
                            
                            $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_ameosfilemanager_domain_model_folder', [
                                'title'      => $entry,
                                'pid'        => 0,
                                'cruser_id'  => $GLOBALS['BE_USER']->user['uid'],
                                'uid_parent' => $uidParent,
                                'crdate'     => time(),
                                'tstamp'     => time(),
                                'deleted'    => 0,
                                'hidden'     => 0,
                                'identifier' => $identifier,
                                'storage'    => $storage,
                            ]);                            
                            $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
                            $this->countFolder++;                            
                        }
                        $this->setDatabaseForFolder($storageFolderPath, $folder . $entry . '/', $uid, $storage);
                    } else {                        
                        $this->setDatabaseForFile($storageFolderPath, $folder, $entry, $uidParent);
                    }
                }
            }
            closedir($handle);

            $currentFolderIdentifier = '/' . trim(str_replace($storageFolderPath, '', $folder), '/') . '/';
            $currentFolderRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
                'tx_ameosfilemanager_domain_model_folder.uid',
                'tx_ameosfilemanager_domain_model_folder',
                'tx_ameosfilemanager_domain_model_folder.identifier like \''. $currentFolderIdentifier . '\'
                    AND (
                        tx_ameosfilemanager_domain_model_folder.storage = 0
                        OR tx_ameosfilemanager_domain_model_folder.storage = ' . $storage . '
                    )'
            );
            if ($currentFolderRecord) {
                $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    'sys_file_metadata.uid, sys_file_metadata.file, sys_file.identifier',
                    'sys_file_metadata JOIN sys_file ON sys_file.uid = sys_file_metadata.file',
                    'sys_file_metadata.folder_uid = ' . $currentFolderRecord['uid'] . '
                        AND sys_file.storage = ' . $storage
                );
                while (($file = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) !== false) {
                    if (!file_exists($storageFolderPath . $file['identifier'])) {
                        $GLOBALS['TYPO3_DB']->exec_DELETEquery('sys_file', 'uid = ' . $file['file']);
                        $GLOBALS['TYPO3_DB']->exec_DELETEquery('sys_file_metadata', 'uid = ' . $file['uid']);
                    }
                }
            }
        }
        
    }

    /**
     *
     * add file into the database
     */
    protected function setDatabaseForFile($storageFolderPath, $folderPath, $entry, $folderParentUid)
    {
        $filePath = $folderPath . $entry;
        $fileIdentifier = str_replace($storageFolderPath, '', $filePath);
        $infoFile = $this->currentStorage->getFileInfoByIdentifier($fileIdentifier, array());

        // Add file into sys_file if it doesn't exist
        $file = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid', 'sys_file', 'identifier LIKE \'' . $fileIdentifier . '\'');
        if ($file) {
            $fileUid = $file['uid'];

            // adding metadatas.
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file_metadata', 'file = ' . $fileUid, ['folder_uid' => $folderParentUid]);
        } else {
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file', [
                'pid'               => 0,
                'tstamp'            => time(),
                'storage'           => $infoFile['storage'],
                'identifier'        => $infoFile['identifier'],
                'identifier_hash'   => $infoFile['identifier_hash'],
                'folder_hash'       => $infoFile['folder_hash'],
                'extension'         => pathinfo($filePath)['extension'] ?: '',
                'mime_type'         => $infoFile['mimetype'],
                'name'              => $entry,
                'size'              => $infoFile['size'],
                'creation_date'     => $infoFile['ctime'],
                'modification_date' => $infoFile['mtime']
            ]);

            $fileUid = $GLOBALS['TYPO3_DB']->sql_insert_id();

            $GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file_metadata', [
                'pid'    => 0,
                'tstamp' => time(),
                'crdate' => time(),
                'file'   => $fileUid,
            ]);
        }
    }
}
