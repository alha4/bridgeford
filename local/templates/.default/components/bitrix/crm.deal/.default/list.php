<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$categoryID = isset($arResult['VARIABLES']['category_id']) ? $arResult['VARIABLES']['category_id'] : -1;

/** @var CMain $APPLICATION */
$APPLICATION->IncludeComponent(
	'bitrix:crm.control_panel',
	'',
	array(
		'ID' => 'DEAL_LIST',
		'ACTIVE_ITEM_ID' => 'DEAL',
		'PATH_TO_COMPANY_LIST' => isset($arResult['PATH_TO_COMPANY_LIST']) ? $arResult['PATH_TO_COMPANY_LIST'] : '',
		'PATH_TO_COMPANY_EDIT' => isset($arResult['PATH_TO_COMPANY_EDIT']) ? $arResult['PATH_TO_COMPANY_EDIT'] : '',
		'PATH_TO_CONTACT_LIST' => isset($arResult['PATH_TO_CONTACT_LIST']) ? $arResult['PATH_TO_CONTACT_LIST'] : '',
		'PATH_TO_CONTACT_EDIT' => isset($arResult['PATH_TO_CONTACT_EDIT']) ? $arResult['PATH_TO_CONTACT_EDIT'] : '',
		'PATH_TO_DEAL_WIDGET' => isset($arResult['PATH_TO_DEAL_WIDGET']) ? $arResult['PATH_TO_DEAL_WIDGET'] : '',
		'PATH_TO_DEAL_LIST' => isset($arResult['PATH_TO_DEAL_LIST']) ? $arResult['PATH_TO_DEAL_LIST'] : '',
		'PATH_TO_DEAL_EDIT' => isset($arResult['PATH_TO_DEAL_EDIT']) ? $arResult['PATH_TO_DEAL_EDIT'] : '',
		'PATH_TO_DEAL_CATEGORY' => isset($arResult['PATH_TO_DEAL_CATEGORY']) ? $arResult['PATH_TO_DEAL_CATEGORY'] : '',
		'PATH_TO_DEAL_WIDGETCATEGORY' => isset($arResult['PATH_TO_DEAL_WIDGETCATEGORY']) ? $arResult['PATH_TO_DEAL_WIDGETCATEGORY'] : '',
		'PATH_TO_LEAD_LIST' => isset($arResult['PATH_TO_LEAD_LIST']) ? $arResult['PATH_TO_LEAD_LIST'] : '',
		'PATH_TO_LEAD_EDIT' => isset($arResult['PATH_TO_LEAD_EDIT']) ? $arResult['PATH_TO_LEAD_EDIT'] : '',
		'PATH_TO_QUOTE_LIST' => isset($arResult['PATH_TO_QUOTE_LIST']) ? $arResult['PATH_TO_QUOTE_LIST'] : '',
		'PATH_TO_QUOTE_EDIT' => isset($arResult['PATH_TO_QUOTE_EDIT']) ? $arResult['PATH_TO_QUOTE_EDIT'] : '',
		'PATH_TO_INVOICE_LIST' => isset($arResult['PATH_TO_INVOICE_LIST']) ? $arResult['PATH_TO_INVOICE_LIST'] : '',
		'PATH_TO_INVOICE_EDIT' => isset($arResult['PATH_TO_INVOICE_EDIT']) ? $arResult['PATH_TO_INVOICE_EDIT'] : '',
		'PATH_TO_ORDER_LIST' => isset($arResult['PATH_TO_ORDER_LIST']) ? $arResult['PATH_TO_ORDER_LIST'] : '',
		'PATH_TO_ORDER_EDIT' => isset($arResult['PATH_TO_ORDER_EDIT']) ? $arResult['PATH_TO_ORDER_EDIT'] : '',
		'PATH_TO_REPORT_LIST' => isset($arResult['PATH_TO_REPORT_LIST']) ? $arResult['PATH_TO_REPORT_LIST'] : '',
		'PATH_TO_DEAL_FUNNEL' => isset($arResult['PATH_TO_DEAL_FUNNEL']) ? $arResult['PATH_TO_DEAL_FUNNEL'] : '',
		'PATH_TO_EVENT_LIST' => isset($arResult['PATH_TO_EVENT_LIST']) ? $arResult['PATH_TO_EVENT_LIST'] : '',
		'PATH_TO_PRODUCT_LIST' => isset($arResult['PATH_TO_PRODUCT_LIST']) ? $arResult['PATH_TO_PRODUCT_LIST'] : '',
		//'COUNTER_EXTRAS' => array('DEAL_CATEGORY_ID' => $categoryID)
	),
	$component
);

if(!Bitrix\Crm\Integration\Bitrix24Manager::isAccessEnabled(CCrmOwnerType::Deal))
{
	$APPLICATION->IncludeComponent('bitrix:bitrix24.business.tools.info', '', array());
}
else
{
	$isBitrix24Template = SITE_TEMPLATE_ID === 'bitrix24';
	if($isBitrix24Template)
	{
		$this->SetViewTarget('below_pagetitle', 0);
	}

	if ($arResult['IS_RECURRING'] !== 'Y')
	{
		$APPLICATION->IncludeComponent("bitrix:crm.entity.counter.panel", "template1", Array(
	"ENTITY_TYPE_NAME" => CCrmOwnerType::DealName,
		"EXTRAS" => array(
			"DEAL_CATEGORY_ID" => $categoryID,
		),
		"PATH_TO_ENTITY_LIST" => $categoryID<0?$arResult["PATH_TO_DEAL_LIST"]:CComponentEngine::makePathFromTemplate($arResult["PATH_TO_DEAL_CATEGORY"],array("category_id"=>$categoryID))
	),
	false
);
	}

	if($isBitrix24Template)
	{
		$this->EndViewTarget();
	}

	if($isBitrix24Template)
	{
		$this->SetViewTarget('inside_pagetitle', 100);
	}
	$catalogPath = ($arResult['IS_RECURRING'] !== 'Y') ? $arResult['PATH_TO_DEAL_CATEGORY'] : $arResult['PATH_TO_DEAL_RECUR_CATEGORY'];

	if(SITE_TEMPLATE_ID === 'bitrix24')
	{
		$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
		$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'crm-toolbar-modifier');
	}

	$APPLICATION->IncludeComponent(
		'bitrix:crm.deal_category.panel',
		$isBitrix24Template ? 'tiny' : '',
		array(
			'PATH_TO_DEAL_LIST' => $arResult['PATH_TO_DEAL_LIST'],
			'PATH_TO_DEAL_EDIT' => $arResult['PATH_TO_DEAL_EDIT'],
			'PATH_TO_DEAL_CATEGORY' => $catalogPath,
			'PATH_TO_DEAL_CATEGORY_LIST' => $arResult['PATH_TO_DEAL_CATEGORY_LIST'],
			'PATH_TO_DEAL_CATEGORY_EDIT' => $arResult['PATH_TO_DEAL_CATEGORY_EDIT'],
			'CATEGORY_ID' => $categoryID
		),
		$component
	);

	if($isBitrix24Template)
	{
		$this->SetViewTarget('inside_pagetitle', 100);
	}

	if ($arResult['RESTRICTED_RECURRING'] !== 'Y')
	{
		$APPLICATION->IncludeComponent(
			'bitrix:crm.entity.list.switcher',
			'',
			array(
				'ENTITY_TYPE' => \CCrmOwnerType::Deal,
				'NAVIGATION_ITEMS' => array(
					array(
						'id' => 'list',
						'name' => GetMessage('CRM_DEAL_LIST_SWITCHER_LIST'),
						'active' =>$arResult['IS_RECURRING'] !== 'Y',
						'url' =>  $categoryID < 0
							? $arResult['PATH_TO_DEAL_LIST']
							: CComponentEngine::makePathFromTemplate(
								$arResult['PATH_TO_DEAL_CATEGORY'],
								array('category_id' => $categoryID)
							)
					),
					array(
						'id' => 'recur',
						'name' => GetMessage('CRM_DEAL_LIST_SWITCHER_RECUR'),
						'active' => $arResult['IS_RECURRING'] === 'Y',
						'url' =>  $categoryID < 0
							? $arResult['PATH_TO_DEAL_RECUR']
							: CComponentEngine::makePathFromTemplate(
								$arResult['PATH_TO_DEAL_RECUR_CATEGORY'],
								array('category_id' => $categoryID)
							)
					)
				)
			),
			$component
		);
	}

	if($isBitrix24Template)
	{
		$this->EndViewTarget();
	}
	$APPLICATION->ShowViewContent('crm-grid-filter');
	$APPLICATION->IncludeComponent(
		'bitrix:crm.deal.menu',
		'',
		array(
			'PATH_TO_DEAL_LIST' => $arResult['PATH_TO_DEAL_LIST'],
			'PATH_TO_DEAL_SHOW' => $arResult['PATH_TO_DEAL_SHOW'],
			'PATH_TO_DEAL_EDIT' => $arResult['PATH_TO_DEAL_EDIT'],
			'PATH_TO_DEAL_FUNNEL' => $arResult['PATH_TO_DEAL_FUNNEL'],
			'PATH_TO_DEAL_IMPORT' => $arResult['PATH_TO_DEAL_IMPORT'],
			'ELEMENT_ID' => 0,
			'CATEGORY_ID' => $categoryID,
			'TYPE' => 'list'
		),
		$component
	);
	if(\Bitrix\Main\ModuleManager::isModuleInstalled('rest'))
	{
		$APPLICATION->IncludeComponent(
			'bitrix:app.placement',
			'menu',
			array(
				'PLACEMENT' => "CRM_DEAL_LIST_MENU",
				"PLACEMENT_OPTIONS" => array(),
				'INTERFACE_EVENT' => 'onCrmDealListInterfaceInit',
				'MENU_EVENT_MODULE' => 'crm',
				'MENU_EVENT' => 'onCrmDealListItemBuildMenu',
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}
?>
<div class="ui-btn-double ui-btn-primary">
 <button id="clean_favorite" class="ui-btn-main">Очистить избранное</button>
</div>

<?if($USER->IsAdmin()):?>

<div class="ui-btn-double ui-btn-primary ui-btn-group">
 <button id="group_cmd" class="ui-btn-main">Управление рекламой</button>
</div>

<script>

function getSelectedObject() {

  return Array.from(document.querySelectorAll('.main-grid-cell-content > .main-grid-checkbox:checked')).map((item) => item.value);

}

function sendCommand(cmd, callback = (agrs) => true) {
	
	BX.ajax.post("/local/ajax/crm_command.php", {

	  "cmd" : cmd,
		"data" : getSelectedObject()

	}, function(resp) {
		
		 callback(resp);
		 
	});

}

BX.bind(BX('group_cmd'), 'click', function(e) {

	BX.SidePanel.Instance.open("/crm/advertising/", {
                                      cacheable : false,
                                      requestMethod : "post",
                                      requestParams  : {
																				sessid  : "<?=bitrix_sessid()?>",
																				data  : getSelectedObject(),
																				type : 'deal'
                                      }
                            });

});

</script>

<?endif?>

<script>

BX.bind(BX('clean_favorite'), 'click', function(e) {

if(confirm('Подтвердить сброс?')) {
 
 BX.showWait(BX('clean_favorite'));
 
	BX.ajax.post('/local/ajax/clean_favorite.php', {"CMD" : "START"} , function(response) {

	 response = JSON.parse(response);

	 if(response.status == 200) {

			location.reload();

	 } else {

			BX.closeWait(BX('clean_favorite'));
			alert('Произошла ошибка, проверьте поле брокер');
		 
	}
 });
}
});

</script>
<?

	$APPLICATION->IncludeComponent(
		'bitrix:crm.deal.list',
		'',
		array(
			'DEAL_COUNT' => '20',
			'IS_RECURRING' => $arResult['IS_RECURRING'],
			'PATH_TO_DEAL_RECUR_SHOW' => $arResult['PATH_TO_DEAL_RECUR_SHOW'],
			'PATH_TO_DEAL_RECUR' => $arResult['PATH_TO_DEAL_RECUR'],
			'PATH_TO_DEAL_RECUR_EDIT' => $arResult['PATH_TO_DEAL_RECUR_EDIT'],
			'PATH_TO_DEAL_LIST' => $arResult['PATH_TO_DEAL_LIST'],
			'PATH_TO_DEAL_SHOW' => $arResult['PATH_TO_DEAL_SHOW'],
			'PATH_TO_DEAL_EDIT' => $arResult['PATH_TO_DEAL_EDIT'],
			'PATH_TO_DEAL_DETAILS' => $arResult['PATH_TO_DEAL_DETAILS'],
			'PATH_TO_DEAL_WIDGET' => $arResult['PATH_TO_DEAL_WIDGET'],
			'PATH_TO_DEAL_KANBAN' => $arResult['PATH_TO_DEAL_KANBAN'],
			'PATH_TO_DEAL_CALENDAR' => $arResult['PATH_TO_DEAL_CALENDAR'],
			'PATH_TO_DEAL_CATEGORY' => $arResult['PATH_TO_DEAL_CATEGORY'],
			'PATH_TO_DEAL_RECUR_CATEGORY' => $arResult['PATH_TO_DEAL_RECUR_CATEGORY'],
			'PATH_TO_DEAL_WIDGETCATEGORY' => $arResult['PATH_TO_DEAL_WIDGETCATEGORY'],
			'PATH_TO_DEAL_KANBANCATEGORY' => $arResult['PATH_TO_DEAL_KANBANCATEGORY'],
			'PATH_TO_DEAL_CALENDARCATEGORY' => $arResult['PATH_TO_DEAL_CALENDARCATEGORY'],
			'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
			'NAVIGATION_CONTEXT_ID' => $arResult['NAVIGATION_CONTEXT_ID'],
			'GRID_ID_SUFFIX' => $categoryID >= 0 ? "C_{$categoryID}" : '',
			'CATEGORY_ID' => $categoryID
		),
		$component
	);
}

\Bitrix\Crm\Integration\Calendar::showCalendarSpotlight();

\Bitrix\Main\Page\Asset::getInstance()->addCss($templateFolder.'/custom.css');
?>
