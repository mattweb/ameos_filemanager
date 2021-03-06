<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$ll = 'LLL:EXT:ameos_filemanager/Resources/Private/Language/locallang_db.xlf:tx_ameosfilemanager_domain_model_folder';

$GLOBALS['TCA']['tx_ameosfilemanager_domain_model_folder'] = [
    'ctrl' => [
        'title'          => $ll,
        'label'          => 'title', 
        'tstamp'         => 'tstamp',
        'crdate'         => 'crdate',
        'cruser_id'      => 'cruser_id',
        'delete'         => 'deleted',
        'enablecolumns'  => ['disabled' => 'hidden', 'fe_group' => 'fe_group_read'],
        'hideTable'      => true,
        'default_sortby' => 'ORDER BY crdate',
        'iconfile'       => ExtensionManagementUtility::extRelPath('ameos_filemanager') . 'Resources/Public/IconsBackend/folder.svg',
        'searchFields'   => 'title, description, keywords',
        'rootLevel'      => 1,
        'security'       => ['ignoreRootLevelRestriction' => 1, 'ignoreWebMountRestriction' => 1],        
    ],
    'feInterface' => ['fe_admin_fieldList' => 'title,description,keywords,fe_groups_access,file,folder,'],
    'types' => ['0' => ['showitem' => 'description,keywords,fe_user_id,fe_group_read,owner_has_read_access,no_read_access,fe_group_write,owner_has_write_access,no_write_access,fe_group_addfolder,fe_group_addfile,status']],
    'interface' => ['showRecordFieldList' => 'title,description,keywords,fe_groups_access,file,folders'],
    'columns' => [
        'hidden' => [        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config'  => ['type' => 'check', 'default' => '0']
        ],
        'crdate' => [
            'exclude' => 0, 
            'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.crdate',
            'config'  => ['type' => 'input']
        ],
        'tstamp' => [
            'exclude' => 0, 
            'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.tstamp',
            'config'  => ['type' => 'input']
        ],
        'cruser_id' => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.be_user',
            'config'  => [
                'type'                => 'select',
                'renderType'          => 'selectSingleBox',
                'size'                => 5,
                'maxitems'            => 1,
                'foreign_table'       => 'be_user',
                'foreign_table_where' => 'ORDER BY be_user.uid'
            ]
        ],
        'fe_user_id' => [
            'exclude' => 0,
            'label'   => $ll . '.fe_user_id',
            'config'  => [
                'type'          => 'select',
                'renderType'    => 'selectSingleBox',
                'maxitems'      => 1,
                'size'          => 10,
                'foreign_table' => 'fe_users',
            ]
        ],
        'title' => [
            'exclude' => 0, 
            'label' => $ll . '.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ]
        ],
        'no_read_access' => [
            'exclude' => 0, 
            'label' => $ll . '.no_read_access',
            'config' => [
                'type'    => 'check',
                'default' => '0',
            ]
        ],
        'no_write_access' => [
            'exclude' => 0, 
            'label'   => $ll . '.no_write_access',
            'config'  => [
                'type'    => 'check',
                'default' => '0',
            ]
        ],
        'owner_has_read_access' => [
            'exclude' => 1,
            'label'   => $ll . '.owner_has_read_access',
            'config'  => [
                'type'    => 'check',
                'default' => '1'
            ]
        ],
        'owner_has_write_access' => [
            'exclude' => 1,
            'label'   => $ll . '.owner_has_write_access',
            'config'  => [
                'type'    => 'check',
                'default' => '1'
            ]
        ],
        'description' => [      
            'exclude' => 0,   
            'label'   => $ll . '.description',     
            'config'  => [
                'type' => 'text', 
                'cols' => '15',
                'rows' => '5', 
                'eval' => 'trim', 
            ]
        ],
        'identifier' => [      
            'exclude' => 0,   
            'label'   => $ll . '.identifier',
            'config'  => [
                'type' => 'text',
                'cols' => '15',
                'rows' => '5',
                'eval' => 'trim', 
            ]
        ],
        'storage' => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:lang/locallang_tca.xlf:sys_file.storage',
            'config'  => [
                'readOnly'            => 1,
                'type'                => 'select',
                'renderType'          => 'selectSingleBox',
                'items'               => [['', 0]],
                'foreign_table'       => 'sys_file_storage',
                'foreign_table_where' => 'ORDER BY sys_file_storage.name',
                'size'                => 1,
                'minitems'            => 0,
                'maxitems'            => 1
            ]
        ],
        'keywords' => [      
            'exclude' => 0,   
            'label'   => $ll . '.keywords',     
            'config'  => [
                'type' => 'text', 
                'cols' => '15',
                'rows' => '5', 
                'eval' => 'trim', 
            ]
        ],
        'fe_group_read' => [
            'exclude' => 1,
            'label'   => $ll . '.fe_group_read',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size'       => 5,
                'maxitems'   => 20,
                'items'      => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.any_login',  -2],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.usergroups', '--div--']
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title'
            ]
        ],
        'fe_group_write' => [
            'exclude' => 1,
            'label'   => $ll . '.fe_group_write',
            'config'  => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 5,
                'maxitems' => 20,
                'items' => [
                        [
                            'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
                            -2
                        ],
                        [
                            'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
                            '--div--'
                        ]
                    ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title'
            ]
        ],
        'fe_group_addfile' => [
            'exclude' => 1,
            'label'   => $ll . '.fe_group_addfile',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size'       => 5,
                'maxitems'   => 20,
                'items'      => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.any_login',  -2],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.usergroups', '--div--']
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title'
            ]
        ],
        'fe_group_addfolder' => [
            'exclude' => 1,
            'label'   => $ll . '.fe_group_addfolder',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size'       => 5,
                'maxitems'   => 20,
                'items'      => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.any_login',  -2],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.usergroups', '--div--']
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title'
            ]
        ],
        'folders' => [ 
            'exclude' => 0,
            'label'   => $ll . '.folders',
            'config'  => [
                'maxitems'      => 500,
                'type'          => 'inline',
                'foreign_table' => 'tx_ameosfilemanager_domain_model_folder',
                'foreign_field' => 'uid_parent',
                'appearance'    => ['collapseAll' => 1],
            ]
        ],
        'uid_parent' => [
            'exclude' => 0,
            'label'   => $ll . '.uid_parent',
            'config'  => [
                'type'                => 'select',
                'renderType'          => 'selectSingleBox',
                'size'                => 5,
                'maxitems'            => 1,
                'foreign_table'       => 'tx_ameosfilemanager_domain_model_folder',
                'foreign_table_where' => 'ORDER BY tx_ameosfilemanager_domain_model_folder.title'
            ]
        ],
        'files' => [
            'exclude' => 0,
            'label'   => $ll . '.files',
            'config'  => [
                'maxitems'      => 500,
                'type'          => 'inline',
                'foreign_table' => 'sys_file',                
            ]
        ],
        'fe_user_id' => [
            'exclude' => 0,
            'label'   => $ll . '.fe_user_id',
            'config'  => [
                'type'          => 'select',
                'maxitems'      => 1,
                'items'         => [['', 0]],
                'size'          => 1,
                'foreign_table' => 'fe_users',
            ]
        ],
        'status' => [
            'exclude' => 1,
            'label'   => $ll . '.status',
            'config'  => [
                'type'          => 'select',
                'maxitems'      => 1,
                'minitems'      => 0,
                'size'          => 1,
                'items'         => [
                    [$ll . '.status.parent',  0],
                    [$ll . '.status.ready',   1],
                    [$ll . '.status.archive', 2],
                ],
            ]
        ],
        'realstatus' => ['config' => ['type' => 'passthrough']]
    ],

];

ExtensionManagementUtility::makeCategorizable('ameos_filemanager', 'tx_ameosfilemanager_domain_model_folder', 'cats', ['exclude' => FALSE]);
