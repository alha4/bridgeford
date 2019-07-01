<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
global $APPLICATION;
CJSCore::RegisterExt('popup_menu', array('js' => array('/bitrix/js/main/popup_menu.js')));
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.buttons.icons");
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/crm.interface.toolbar/templates/slider/style.css');

$toolbarID = $arParams['TOOLBAR_ID'];
$prefix =  $toolbarID.'_';

$items = array();
$moreItems = array();
$communicationPanel = null;
$documentButton = null;
$enableMoreButton = false;

foreach($arParams['BUTTONS'] as $item)
{
	if(!$enableMoreButton && isset($item['NEWBAR']) && $item['NEWBAR'] === true)
	{
		$enableMoreButton = true;
		continue;
	}

	if(isset($item['TYPE']) && $item['TYPE'] === 'crm-communication-panel')
	{
		$communicationPanel = $item;
		continue;
	}

	if(isset($item['TYPE']) && $item['TYPE'] === 'crm-document-button')
	{
		$documentButton = $item;
		continue;
	}

	if($enableMoreButton)
	{
		$moreItems[] = $item;
	}
	else
	{
		$items[] = $item;
	}
}

$this->SetViewTarget('inside_pagetitle', 10000);

?><div id="<?=htmlspecialcharsbx($toolbarID)?>" class="pagetitle-container pagetitle-align-right-container">

<?if(!$arParams['ENTITY_TYPE_ID']) : ?>

<div class="ui-btn-double ui-btn-primary">
  <button id="search_object" class="ui-btn-main">Подобрать объекты</button>
</div> 

<div class="ui-btn-double ui-btn-primary">
 <button id="add_contact" class="ui-btn-main">Добавить контакт</button>
</div> 

<?php

 $ticket = array_pop(explode('_', $arParams['TOOLBAR_ID']));


?>
<script>

BX.bind(BX('add_contact'),'click', function(e) {
		
		BX.SidePanel.Instance.open("/crm/contact/details/0/", {
                                      cacheable : false,
                                      requestMethod : "post",
                                      requestParams  : {
                                        sessid  : "<?=bitrix_sessid()?>"
                                      }
                                    });

});

BX.bind( BX('search_object'), 'click', function(e) {
		
		BX.SidePanel.Instance.open("/crm/search/ticket/" , {
                                      cacheable : false,
                                      requestMethod : "post",
                                      requestParams  : {
																				sessid  : "<?=bitrix_sessid()?>",
																				id  : <?=$ticket ?>,
																				type : 'deal'
                                      }
                                    });

	});

</script>

<?endif?>
<?if($arParams['ENTITY_TYPE_ID'] == CCrmOwnerType::Deal) : ?>

<?
 
 $uf = new CUserTypeManager();
 $brokerID = $uf->GetUserFieldValue('CRM_DEAL','UF_CRM_1540886934', $arParams['ENTITY_ID']);

?>

<div class="ui-btn-double ui-btn-primary">
 <button id="actuality_object" class="ui-btn-main">Актуализировать</button>
</div>
<div class="ui-btn-double ui-btn-primary">
 <button id="actuality_map" class="ui-btn-main">Обновить карту</button>
</div>
<div class="ui-btn-double ui-btn-primary">
<button id="toolbar_pdf_<?=$arParams['ENTITY_ID']?>" class="ui-btn ui-btn-md ui-btn-light-border ui-btn-dropdown ui-btn-themes crm-btn-dropdown-document">PDF экcпорт</button>
</div> 

 <div class="ui-btn-double ui-btn-primary">
 <button id="update_price" class="ui-btn-main">Обновить стратегии</button>
 </div> 
 <div class="ui-btn-double ui-btn-primary">
 <button id="add_contact" class="ui-btn-main">Добавить контакт</button>
 </div> 
 <div class="ui-btn-double ui-btn-primary">
 <button id="search_similar" class="ui-btn-main">Подобрать похожие</button>
 </div> 
 <div class="ui-btn-double ui-btn-primary">
  <button id="search_ticket" class="ui-btn-main">Подобрать заявки</button>
 </div> 
<script>

document.querySelector('#actuality_map').addEventListener('click', function(e) {

  BX.ajax.post("/local/ajax/map.php", `id=<?=$arParams['ENTITY_ID']?>` , function(resp) {
		
		if(JSON.parse(resp).status == 200) {

			  BX.SidePanel.Instance.getTopSlider().getFrameWindow().location.reload();

		} else {

        alert('ошибка обновления карты');

		}
		
	});


}, false);

var PDF = {};

PDF.generate = function(typePDF) {

	BX.SidePanel.Instance.open("/local/mpdf/pdf_export.php", {
                                      cacheable : false,
                                      requestMethod : "post",
                                      requestParams  : {
																				sessid  : "<?=bitrix_sessid()?>",
																				doc_id  : <?=$arParams['ENTITY_ID']?>,
																				template : typePDF 
                                      }
																		});
																		
	

																
	setTimeout(function() {
			BX.SidePanel.Instance.close();
	},8000);																
  
};

</script>
 <?
	 $pdfItems = [];

	 $pdfItems[] = array(

		 'ID' => 1,
		 'SORT' => 1,
		 'MODULE_ID' => 'crm',
		 'text' => 'Без шапки',
		 'CODE' => 'OUT_HEAD',
		 'ACTIVE' => 'Y',
		 'onclick' => sprintf("PDF.generate('%s')", 'OUT_HEAD')

	 );

	 $pdfItems[] = array(

		'ID' => 2,
		'SORT' => 2,
		'MODULE_ID' => 'crm',
		'text' => 'Только шапка',
		'CODE' => 'ONLY_HEAD',
		'ACTIVE' => 'Y',
		'onclick' => sprintf("PDF.generate('%s')", 'ONLY_HEAD')


	);

	$pdfItems[] = array(

		'ID' =>3,
		'SORT' => 3,
		'MODULE_ID' => 'crm',
		'text' => 'Шапка + брокер',
		'CODE' => 'BROKER_HEAD',
		'ACTIVE' => 'Y',
		'onclick' => sprintf("PDF.generate('%s')", 'BROKER_HEAD')

	);

	$pdfItems[] = array(

		'ID' => 4,
		'SORT' => 4,
		'MODULE_ID' => 'crm',
		'text' => 'Только брокер',
		'CODE' => 'BROKER_ONLY',
		'ACTIVE' => 'Y',
		'onclick' => sprintf("PDF.generate('%s')",  'BROKER_ONLY')

	);

	$PdfButtonId = 'toolbar_pdf_'.$arParams['ENTITY_ID'];
 
 ?>
 <script>
	"use strict";

  BX.bind(BX('<?=$PdfButtonId?>'),'click', function(e) {
		
     BX.PopupMenu.show('<?=CUtil::JSEscape($PdfButtonId);?>_menu', BX('<?=CUtil::JSEscape($PdfButtonId);?>'), 
		      <?=CUtil::PhpToJSObject($pdfItems);?>, {
					offsetLeft: 0,
					offsetTop: 0,
					closeByEsc: true,
					className: 'document-toolbar-menu'
				});

  });

	BX.bind(BX('search_ticket'),'click', function(e) {
		
		BX.SidePanel.Instance.open("/crm/search/lead/", {
                                      cacheable : false,
                                      requestMethod : "post",
                                      requestParams  : {
																				sessid  : "<?=bitrix_sessid()?>",
																				id  : <?=$arParams['ENTITY_ID']?>,
																				type : 'lead'
                                      }
                                    });

	});

	BX.bind(BX('search_similar'),'click', function(e) {
		
		BX.SidePanel.Instance.open("/crm/search/", {
                                      cacheable : false,
                                      requestMethod : "post",
                                      requestParams  : {
																				sessid  : "<?=bitrix_sessid()?>",
																				id  : <?=$arParams['ENTITY_ID']?>,
																				type : 'deal'
                                      }
                                    });

	});

	BX.bind(BX('add_contact'),'click', function(e) {
		
		BX.SidePanel.Instance.open("/crm/contact/details/0/", {
                                      cacheable : false,
                                      requestMethod : "post",
                                      requestParams  : {
                                        sessid  : "<?=bitrix_sessid()?>"
                                      }
                                    });

	});
	
	 BX.bind(BX('actuality_object'), 'click', function(e) {

		if(confirm('Актуализировать объект сегодняшней датой?')) {
		 
		 BX.showWait(BX('actuality_object'));
		 
	 	 BX.ajax.post('/local/ajax/actuality_object.php', {'id' : <?=$arParams['ENTITY_ID']?>} , function(response) {

       response = JSON.parse(response);

       if(response.status) {

	        location.reload();
	 
       } else {

					BX.closeWait(BX('actuality_object'));
					alert('Произошла ошибка, проверьте поле брокер');
	       
      }
		 });
		}
	 });

	 BX.bind( BX('update_price'), 'click', function(e) {

		 BX.showWait(BX('update_price'));
		 
      BX.ajax.post('/local/ajax/cian_strategy.php', {'id' : <?=$arParams['ENTITY_ID']?>} , function(response) {

				response = JSON.parse(response);

				if(response.status) {

					 location.reload();
					 
				} else {

					 BX.closeWait(BX('update_price'));
					 
					 let error_mess = 'Нет объявлений, проверьте раздел География и площадь, возможно не установлен флаг [Активировать автоматическое ценообразование]';

					 if(response.error) {

						error_mess+= ', или ' + response.error;

					 }

           alert(	error_mess );
				}
				
			});
 
	 }); 

</script>
<?endif;?>
<?
if($communicationPanel)
{
		$data = isset($communicationPanel['DATA']) && is_array($communicationPanel['DATA']) ? $communicationPanel['DATA'] : array();
		$multifields = isset($data['MULTIFIELDS']) && is_array($data['MULTIFIELDS']) ? $data['MULTIFIELDS'] : array();

		$enableCall = !(isset($data['ENABLE_CALL']) && $data['ENABLE_CALL'] === false);

		$phones = isset($multifields['PHONE']) && is_array($multifields['PHONE']) ? $multifields['PHONE'] : array();
		$emails = isset($multifields['EMAIL']) && is_array($multifields['EMAIL']) ? $multifields['EMAIL'] : array();
		$messengers = isset($multifields['IM']) && is_array($multifields['IM']) ? $multifields['IM'] : array();

		$callButtonId = "{$toolbarID}_call" ;
		$messengerButtonId = "{$toolbarID}_messenger" ;
		$emailButtonId = "{$toolbarID}_email" ;

		$ownerInfo = isset($data['OWNER_INFO']) && is_array($data['OWNER_INFO']) ? $data['OWNER_INFO'] : array();
		?>
		<div class="crm-entity-actions-container">
			<?if(!$enableCall || empty($phones))
			{
				?><div id="<?=htmlspecialcharsbx($callButtonId)?>" class="webform-small-button webform-small-button-transparent crm-contact-menu-call-icon-not-available"></div><?
			}
			else
			{
				?><div id="<?=htmlspecialcharsbx($callButtonId)?>" class="webform-small-button webform-small-button-transparent crm-contact-menu-call-icon"></div><?
			}?>
			<script type="text/javascript">
				BX.ready(
					function()
					{
						BX.InterfaceToolBarPhoneButton.messages =
						{
							telephonyNotSupported: "<?=GetMessageJS('CRM_TOOLBAR_TELEPHONY_NOT_SUPPORTED')?>"
						};
						BX.InterfaceToolBarPhoneButton.create(
							this._id + "_call",
							{
								button: BX("<?=CUtil::JSEscape($callButtonId)?>"),
								data: <?=CUtil::PhpToJSObject($phones)?>,
								ownerInfo: <?=CUtil::PhpToJSObject($ownerInfo)?>
							}
						);
					}
				);
			</script>
			<!--<div class="webform-small-button webform-small-button-transparent crm-contact-menu-sms-icon-not-available"></div>-->
			<?if(empty($emails))
			{
				?><div id="<?=htmlspecialcharsbx($emailButtonId)?>" class="webform-small-button webform-small-button-transparent crm-contact-menu-mail-icon-not-available"></div><?
			}
			else
			{
				?><div id="<?=htmlspecialcharsbx($emailButtonId)?>" class="webform-small-button webform-small-button-transparent crm-contact-menu-mail-icon"></div><?
			}?>
			<script type="text/javascript">
				BX.ready(
					function()
					{
						BX.InterfaceToolBarEmailButton.create(
							this._id + "_email",
							{
								button: BX("<?=CUtil::JSEscape($emailButtonId)?>"),
								data: <?=CUtil::PhpToJSObject($emails)?>,
								ownerInfo: <?=CUtil::PhpToJSObject($ownerInfo)?>
							}
						);
					}
				);
			</script>
			<?if(empty($messengers))
			{
				?><div id="<?=htmlspecialcharsbx($messengerButtonId)?>" class="webform-small-button webform-small-button-transparent crm-contact-menu-im-icon-not-available"></div><?
			}
			else
			{
				?><div id="<?=htmlspecialcharsbx($messengerButtonId)?>" class="webform-small-button webform-small-button-transparent crm-contact-menu-im-icon"></div><?
			}?>
			<script type="text/javascript">
				BX.ready(
					function()
					{
						BX.InterfaceToolBarMessengerButton.create(
							this._id + "_im",
							{
								button: BX("<?=CUtil::JSEscape($messengerButtonId)?>"),
								data: <?=CUtil::PhpToJSObject($messengers)?>,
								ownerInfo: <?=CUtil::PhpToJSObject($ownerInfo)?>
							}
						);
					}
				);
			</script>
		</div>
		<?
}


?>
<button class="ui-btn ui-btn-md ui-btn-light-border ui-btn-themes ui-btn-icon-setting crm-btn-cogwheel"></button>
<script type="text/javascript">
	BX.ready(
		function ()
		{
			BX.InterfaceToolBar.create(
				"<?=CUtil::JSEscape($toolbarID)?>",
				BX.CrmParamBag.create(
					{
						"containerId": "<?=CUtil::JSEscape($toolbarID)?>",
						"items": <?=CUtil::PhpToJSObject($moreItems)?>,
						"moreButtonClassName": "crm-btn-cogwheel"
					}
				)
			);
		}
	);
</script><?
$documentButton = null;
if($documentButton)
{
	$documentButtonId = $toolbarID.'_document';
	?>
	<button class="ui-btn ui-btn-md ui-btn-light-border ui-btn-dropdown ui-btn-themes crm-btn-dropdown-document" id="<?=htmlspecialcharsbx($documentButtonId);?>"><?=$documentButton['TEXT'];?></button>
	<script>
		BX.ready(function()
		{
			BX.bind(BX('<?=CUtil::JSEscape($documentButtonId);?>'), 'click', function()
			{
				BX.PopupMenu.show('<?=CUtil::JSEscape($documentButtonId);?>_menu', BX('<?=CUtil::JSEscape($documentButtonId);?>'), <?=CUtil::PhpToJSObject($documentButton['ITEMS']);?>, {
					offsetLeft: 0,
					offsetTop: 0,
					closeByEsc: true,
					className: 'document-toolbar-menu'
				});
			});
		});
	</script>
	<?
}

foreach($items as $item)
{
	$type = isset($item['TYPE']) ? $item['TYPE'] : '';
	$code = isset($item['CODE']) ? $item['CODE'] : '';
	$visible = isset($item['VISIBLE']) ? (bool)$item['VISIBLE'] : true;
	$text = isset($item['TEXT']) ? htmlspecialcharsbx($item['TEXT']) : '';
	$title = isset($item['TITLE']) ? htmlspecialcharsbx($item['TITLE']) : '';
	$link = isset($item['LINK']) ? htmlspecialcharsbx($item['LINK']) : '#';
	$icon = isset($item['ICON']) ? htmlspecialcharsbx($item['ICON']) : '';
	$onClick = isset($item['ONCLICK']) ? htmlspecialcharsbx($item['ONCLICK']) : '';

	if($type === 'crm-context-menu')
	{
		$menuItems = isset($item['ITEMS']) && is_array($item['ITEMS']) ? $item['ITEMS'] : array();

		?><div class="webform-small-button webform-small-button-blue webform-button-icon-triangle-down crm-btn-toolbar-menu"<?=$onClick !== '' ? " onclick=\"{$onClick}; return false;\"" : ''?>>
			<span class="webform-small-button-text"><?=$text?></span>
			<span class="webform-button-icon-triangle"></span>
		</div><?

		if(!empty($menuItems))
		{
			?><script type="text/javascript">
				BX.ready(
					function()
					{
						BX.InterfaceToolBar.create(
							"<?=CUtil::JSEscape($toolbarID)?>",
							BX.CrmParamBag.create(
								{
									"containerId": "<?=CUtil::JSEscape($toolbarID)?>",
									"prefix": "",
									"menuButtonClassName": "crm-btn-toolbar-menu",
									"items": <?=CUtil::PhpToJSObject($menuItems)?>
								}
							)
						);
					}
				);
			</script><?
		}
	}
	elseif($type == 'toolbar-conv-scheme')
	{
		$params = isset($item['PARAMS']) ? $item['PARAMS'] : array();

		$typeID = isset($params['TYPE_ID']) ? (int)$params['TYPE_ID'] : 0;
		$schemeName = isset($params['SCHEME_NAME']) ? $params['SCHEME_NAME'] : null;
		$schemeDescr = isset($params['SCHEME_DESCRIPTION']) ? $params['SCHEME_DESCRIPTION'] : null;
		$name = isset($params['NAME']) ? $params['NAME'] : $code;
		$entityID = isset($params['ENTITY_ID']) ? (int)$params['ENTITY_ID'] : 0;
		$entityTypeID = isset($params['ENTITY_TYPE_ID']) ? (int)$params['ENTITY_TYPE_ID'] : CCrmOwnerType::Undefined;
		$isPermitted = isset($params['IS_PERMITTED']) ? (bool)$params['IS_PERMITTED'] : false;
		$lockScript = isset($params['LOCK_SCRIPT']) ? $params['LOCK_SCRIPT'] : '';

		$options = CUserOptions::GetOption("crm.interface.toobar", "conv_scheme_selector", array());
		$hintKey = 'enable_'.strtolower($name).'_hint';
		$enableHint = !(isset($options[$hintKey]) && $options[$hintKey] === 'N');
		$hint = isset($params['HINT']) ? $params['HINT'] : array();

		$iconBtnClassName = $isPermitted ? 'crm-btn-convert' : 'crm-btn-convert crm-btn-convert-blocked';
		$originUrl = $APPLICATION->GetCurPage();

		$labelID = "{$prefix}{$code}_label";
		$buttonID = "{$prefix}{$code}_button";

		if($isPermitted && $entityTypeID === CCrmOwnerType::Lead)
		{
			Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/crm.js');
		}

		?>
		<div class="ui-btn-double ui-btn-primary" style="display:none">
			<button id="<?=htmlspecialcharsbx($labelID);?>" class="ui-btn-main"><?=htmlspecialcharsbx($schemeDescr)?></button>
			<button id="<?=htmlspecialcharsbx($buttonID);?>" class="ui-btn-extra"></button>
		</div>
		<script type="text/javascript">
			BX.ready(
				function()
				{
					//region Toolbar script
					<?$selectorID = CUtil::JSEscape($name);?>
					<?$originUrl = CUtil::JSEscape($originUrl);?>
					<?if($isPermitted):?>
						<?if($entityTypeID === CCrmOwnerType::Lead):?>
							BX.CrmLeadConversionSchemeSelector.create(
								"<?=$selectorID?>",
								{
									typeId: <?=$typeID?>,
									entityId: <?=$entityID?>,
									scheme: "<?=$schemeName?>",
									containerId: "<?=$labelID?>",
									labelId: "<?=$labelID?>",
									buttonId: "<?=$buttonID?>",
									originUrl: "<?=$originUrl?>",
									enableHint: <?=CUtil::PhpToJSObject($enableHint)?>,
									hintMessages: <?=CUtil::PhpToJSObject($hint)?>
								}
							);
						<?elseif($entityTypeID === CCrmOwnerType::Deal):?>
							BX.CrmDealConversionSchemeSelector.create(
								"<?=$selectorID?>",
								{
									entityId: <?=$entityID?>,
									scheme: "<?=$schemeName?>",
									containerId: "<?=$labelID?>",
									labelId: "<?=$labelID?>",
									buttonId: "<?=$buttonID?>",
									originUrl: "<?=$originUrl?>",
									enableHint: <?=CUtil::PhpToJSObject($enableHint)?>,
									hintMessages: <?=CUtil::PhpToJSObject($hint)?>
								}
							);

							BX.addCustomEvent(window,
								"CrmCreateQuoteFromDeal",
								function()
								{
									BX.CrmDealConverter.getCurrent().convert(
										<?=$entityID?>,
										BX.CrmDealConversionScheme.createConfig(BX.CrmDealConversionScheme.quote),
										"<?=$originUrl?>"
									);
								}
							);
							BX.addCustomEvent(window,
								"CrmCreateInvoiceFromDeal",
								function()
								{
									BX.CrmDealConverter.getCurrent().convert(
										<?=$entityID?>,
										BX.CrmDealConversionScheme.createConfig(BX.CrmDealConversionScheme.invoice),
										"<?=$originUrl?>"
									);
								}
							);
						<?elseif($entityTypeID === CCrmOwnerType::Quote):?>
							BX.CrmQuoteConversionSchemeSelector.create(
								"<?=$selectorID?>",
								{
									entityId: <?=$entityID?>,
									scheme: "<?=$schemeName?>",
									containerId: "<?=$labelID?>",
									labelId: "<?=$labelID?>",
									buttonId: "<?=$buttonID?>",
									originUrl: "<?=$originUrl?>",
									enableHint: <?=CUtil::PhpToJSObject($enableHint)?>,
									hintMessages: <?=CUtil::PhpToJSObject($hint)?>
								}
							);

							BX.addCustomEvent(window,
								"CrmCreateDealFromQuote",
								function()
								{
									BX.CrmQuoteConverter.getCurrent().convert(
										<?=$entityID?>,
										BX.CrmQuoteConversionScheme.createConfig(BX.CrmQuoteConversionScheme.deal),
										"<?=$originUrl?>"
									);
								}
							);

							BX.addCustomEvent(window,
								"CrmCreateInvoiceFromQuote",
								function()
								{
									BX.CrmQuoteConverter.getCurrent().convert(
										<?=$entityID?>,
										BX.CrmQuoteConversionScheme.createConfig(BX.CrmQuoteConversionScheme.invoice),
										"<?=$originUrl?>"
									);
								}
							);
						<?endif;?>
					<?elseif($lockScript !== ''):?>
						var showLockInfo = function()
						{
							<?=$lockScript?>
						};
						BX.bind(BX("<?=$labelID?>"), "click", showLockInfo );
						<?if($entityTypeID === CCrmOwnerType::Deal):?>
							BX.addCustomEvent(window, "CrmCreateQuoteFromDeal", showLockInfo);
							BX.addCustomEvent(window, "CrmCreateInvoiceFromDeal", showLockInfo);
						<?elseif($entityTypeID === CCrmOwnerType::Quote):?>
							BX.addCustomEvent(window, "CrmCreateDealFromQuote", showLockInfo);
							BX.addCustomEvent(window, "CrmCreateInvoiceFromQuote", showLockInfo);
						<?endif;?>
					<?endif;?>
					//endregion
				}
			);
		</script><?
	}
	elseif($type == 'bizproc-starter-button')
	{
		$hasTemplates = is_array($item['DATA']['templates']) && count($item['DATA']['templates']) > 0;
		if ($hasTemplates):

			CJSCore::Init('bp_starter');
			$starterButtonId = "{$toolbarID}_bp_starter";
		?>
		<span class="webform-small-button webform-small-button-transparent crm-bizproc-starter-icon"
			id="<?=htmlspecialcharsbx($starterButtonId)?>" title="<?=GetMessage('CRM_TOOLBAR_BIZPROC_STARTER_LABEL')?>">
		</span>
		<script type="text/javascript">
			BX.ready(
				function()
				{
					var button = BX('<?=CUTil::JSEscape($starterButtonId)?>');
					if (button)
					{
						var config = <?=\Bitrix\Main\Web\Json::encode($item['DATA'])?>;
						if (config.templates && config.templates.length > 0)
						{
							var starter = new BX.Bizproc.Starter(config);
							BX.bind(button, 'click', function(e)
							{
								starter.showTemplatesMenu(button);
							});
						}
					}
				}
			);
		</script>
		<?
		endif;
	}
	else
	{
		?><a target="_top" class="webform-small-button webform-small-button-blue crm-top-toolbar-add<?=$icon !== '' ? " {$icon}" : ''?>" href="<?=$link?>" title="<?=$title?>"<?=$onClick !== '' ? " onclick=\"{$onClick}; return false;\"" : ''?>><?=$text?></a><?
	}
}
?></div><?
$this->EndViewTarget();