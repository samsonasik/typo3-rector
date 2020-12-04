<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\ValueObject\RenameClassConstant;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Ssch\TYPO3Rector\Rector\v7\v6\AddRenderTypeToSelectFieldRector;
use Ssch\TYPO3Rector\Rector\v7\v6\MigrateT3editorWizardToRenderTypeT3editorRector;
use Ssch\TYPO3Rector\Rector\v7\v6\RemoveIconOptionForRenderTypeSelectRector;
use Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector;
use Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector;
use Ssch\TYPO3Rector\Rector\v8\v4\SubstituteOldWizardIconsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\IndexedSearch\Controller\SearchFormController;
use TYPO3\CMS\IndexedSearch\Domain\Repository\IndexSearchRepository;
use TYPO3\CMS\IndexedSearch\Utility\LikeWildcard;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../services.php');
    $services = $containerConfigurator->services();
    $services->set(RenamePiListBrowserResultsRector::class);
    $services->set(MethodCallToStaticCallRector::class)->call('configure', [[
        MethodCallToStaticCallRector::METHOD_CALLS_TO_STATIC_CALLS => inline_value_objects([
            new MethodCallToStaticCall(
                DocumentTemplate::class,
                'issueCommand',
                BackendUtility::class,
                'getLinkToDataHandlerAction'
            ),
        ]),
    ]]);
    $services->set(RenameClassConstantRector::class)->call('configure', [[
        RenameClassConstantRector::CLASS_CONSTANT_RENAME => inline_value_objects([
            new RenameClassConstant(
                SearchFormController::class,
                'WILDCARD_LEFT',
                LikeWildcard::class . '::WILDCARD_LEFT'
            ),
            new RenameClassConstant(
                SearchFormController::class,
                'WILDCARD_RIGHT',
                LikeWildcard::class . '::WILDCARD_RIGHT'
            ),
            new RenameClassConstant(
                IndexSearchRepository::class,
                'WILDCARD_LEFT',
                LikeWildcard::class . '::WILDCARD_LEFT'
            ),
            new RenameClassConstant(
                IndexSearchRepository::class,
                'WILDCARD_RIGHT',
                LikeWildcard::class . '::WILDCARD_RIGHT'
            ),
        ]),
    ]]);
    $services->set(WrapClickMenuOnIconRector::class);
    $services->set(MigrateT3editorWizardToRenderTypeT3editorRector::class);
    $services->set(SubstituteOldWizardIconsRector::class)->call('configure', [[
        SubstituteOldWizardIconsRector::OLD_TO_NEW_FILE_LOCATIONS => [
            'add.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif',
            'link_popup.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
            'wizard_rte2.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif',
            'wizard_table.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_table.gif',
            'edit2.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
            'list.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_list.gif',
            'wizard_forms.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_forms.gif',
        ],
        ]]);
    $services->set(AddRenderTypeToSelectFieldRector::class);
    $services->set(RemoveIconOptionForRenderTypeSelectRector::class);
};
