Object.assign( BX.Crm.EntityEditor.prototype, {

  initializeTicketCustom : function() {

    console.log('start event handler');

    const TicketModel = this.prepareModel({
				 
      'edit' : {

          ON_SEARCH :    {value : 359, enumerable : true},
          ON_OBJECT :    {value : 360, enumerable : true},
          ON_PERMANENT : {value : 361, enumerable : true}
      },
      'view' : {

         ON_SEARCH :    {value : 'Заявка на поиск',   enumerable : true},
         ON_OBJECT :    {value : 'Заявка по объекту', enumerable : true},
         ON_PERMANENT : {value : 'Постоянная заявка', enumerable : true}

      }
     
    });
     
    for(key in TicketModel) {
       
       this._inits[TicketModel[key]] = [];
       this._views[TicketModel[key]] = [];
     
    }

    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeBuildingTypeEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeNeedGeoEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeOSZEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializePayCommisionEvent');
  //  this.registerEventListener(TicketModel.ON_SEARCH, 'showPaybackCashingFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeCalculateCostEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeTiketRentPriceEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeTicketNDSEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showClientContactFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showStatusTiketFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializePlannedRunEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showPlannedRunFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showAllFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showCommercFields');


    if(!this._entityId) {

       this.hideFormEdit();
       this.initializeEventListener();
    }

    if(this._entityId > 0) {

      this.registerView(TicketModel.ON_SEARCH, 'showTicketGeoFields');
      this.registerView(TicketModel.ON_SEARCH, 'showOSZFields');
  //    this.registerView(TicketModel.ON_SEARCH, 'showCommisionFields');  убрали поле Платит ли комиссию
      this.registerView(TicketModel.ON_SEARCH, 'showClientContactFields');
      this.registerView(TicketModel.ON_SEARCH, 'showPlannedRunFields');
      this.registerView(TicketModel.ON_SEARCH, 'showCommercFields');
   
      setTimeout(() => {
 
        this.initializeViews(); 
        this.showSections();
        this.showAllFields();
        this.showCommercFields();

       }, 500);

   }   

  },

  hideFormEdit : function() {

    const allSections = document.querySelectorAll('div[id^="section_"]'),
          viewModel = ['section_o_zayavke'],
          ticketModel = this.prepareModel({
				 
            'edit' : {

              ON_SEARCH :    {value : 359, enumerable : true},
              ON_OBJECT :    {value : 360, enumerable : true},
              ON_PERMANENT : {value : 361, enumerable : true}
          }});

    if(this.getTicketCategoryID() == ticketModel.ON_OBJECT) {

      for(var section of allSections) {

        if(viewModel.indexOf(section.id) == -1) {

           section.parentNode.style.display = 'none';

        }

      }

    } else {

      for(var section of allSections) {

        section.parentNode.style.display = 'block';

      }

    }
 
  },

  getTicketCategoryID : function() {

    if(this.getMode() === BX.Crm.EntityEditorMode.edit && this.nodeSelect('UF_CRM_1545389896')) {

       return this.nodeSelectValue(this.nodeSelect('UF_CRM_1545389896'));

    }

    return this.getTextValue(this.node('UF_CRM_1545389896'));

  },

  getTypeBuilding : function() {

   if(this.nodeSelect('UF_CRM_1545389958')) {

      return parseInt(this.nodeSelectValue(this.nodeSelect('UF_CRM_1545389958')));

   }

   return this.getTextValue(this.node('UF_CRM_1545389958'));

  },

  showCommercFields : function() {

    const fieldsRent = ['UF_CRM_1547218667182','UF_CRM_1547218608920','UF_CRM_1547121130737','UF_CRM_1547631768634','UF_CRM_1565276254256'], //,'UF_CRM_1547218758','UF_CRM_1547218826' - убрал поля

	  fieldsSale = ['UF_CRM_1565853455'],

          fieldsRentBusiness = ['UF_CRM_1565853455','UF_CRM_1547218667182','UF_CRM_1547628348754','UF_CRM_1565250284','UF_CRM_1547218608920','UF_CRM_1547121130737','UF_CRM_1547631768634','UF_CRM_1565276254256'];

    if(this.getTypeBuilding() == 362 ||
       this.getTypeBuilding() == 'Помещение в аренду') {
    
       for(var uf of fieldsRent) {

          this.showField(this.node(uf));

       }

    } else if(this.getTypeBuilding() == 363 ||
              this.getTypeBuilding() == 'Помещение на продажу') {

        for(var uf of fieldsSale) {

           this.showField(this.node(uf));
        
        }
    
    } else if(this.getTypeBuilding() == 364 ||
              this.getTypeBuilding() == 'Арендный бизнес') {

        for(var uf of fieldsRentBusiness) {

           this.showField(this.node(uf));
        
        }
    }
  },

  hideSections : function() {

    this.hideField(this.nodeSection('.crm-section_arendator'));

    switch(this.getTypeBuilding()) {


      case 'Арендный бизнес' :
      case 364 :

      break;

      case 'Помещение на продажу' :
      case 363 :


      break;

      case 'Помещение в аренду' :
      case 362 :


      break;


   }


  },

  showSections : function() {


     switch(this.getTypeBuilding()) {


        case 'Арендный бизнес' :
        case 364 :

            this.showField(this.nodeSection('.crm-section_arendator'));

        break;

        case 'Помещение на продажу' :
        case 363 :


        break;

        case 'Помещение в аренду' :
        case 362 :


        break;


     }
  },

  showAllFields : function() {


    switch(this.getTypeBuilding()) {


       case 'Арендный бизнес' :
       case 364 :

           this.showField(this.node('UF_CRM_1547631768634'));
           this.showField(this.node('UF_CRM_1547631814802'));

       break;

       case 'Помещение на продажу' :
       case 363 :


       break;

       case 'Помещение в аренду' :
       case 362 :


       break;


    }

  },

  nodeSection : function(exp) {

      return document.querySelector(`${exp}`);

  },

  renameNode : function(node, nodeName) {

    if(node) {

       node.querySelector('.crm-entity-widget-content-block-title-text').textContent = nodeName;

    }

  },

  initializeBuildingTypeEvent : function() {

    this.bindEvent(this.nodeSelect('UF_CRM_1545389958'),'change', this.onBuildingTypeChange);
  
  },

  onBuildingTypeChange : function() {

    this.buildingTypeView();

  },

  buildingTypeView : function() {

    const rentBusiness = ['UF_CRM_1565853455','UF_CRM_1547218667182','UF_CRM_1565854434','UF_CRM_1547631768634','UF_CRM_1547628348754','UF_CRM_1565250284','UF_CRM_1547218608920','UF_CRM_1547121130737','UF_CRM_1547631768634','UF_CRM_1565276254256'], // добавляем поля для направлений
          rent = ['UF_CRM_1547218667182','UF_CRM_1547218608920','UF_CRM_1547121130737','UF_CRM_1547631768634','UF_CRM_1565276254256'],
          sale = ['UF_CRM_1565853455'];

    for(code of rentBusiness) {

      this.hideField(this.node(code));

    }

    this.renameNode(this.node('UF_CRM_1547551210'), 'Стоимость аренды');
    this.renameNode(this.node('UF_CRM_1565250601'), 'Стоимость аренды До');

    this.hideSections();
    this.showSections();

    switch(this.getTypeBuilding()) {


      case 'Арендный бизнес' :
      case 364 :

      for(code of rentBusiness) {

        this.showField(this.node(code));
  
      }

      this.renameNode(this.node('UF_CRM_1547551210'), 'Стоимость объекта');
      this.renameNode(this.node('UF_CRM_1565250601'), 'Стоимость объекта До');
      
      break;

      case 'Помещение на продажу' :
      case 363 :

      this.renameNode(this.node('UF_CRM_1547551210'), 'Стоимость объекта');
      this.renameNode(this.node('UF_CRM_1565250601'), 'Стоимость объекта До');

      for(code of sale) {

        this.showField(this.node(code));
  
      }

      break;

      case 'Помещение в аренду' :
      case 362 :

      for(code of rent) {

          this.showField(this.node(code));
      
      }

      this.hideField(this.node('UF_CRM_1565853455'));

      break;

   }

  },

  initializeNeedGeoEvent : function() {

    this._regionTicketSelect = this.nodeSelect("UF_CRM_1545390144");

    this.bindEvent(this._regionTicketSelect, 'change', this.onRegionTicketChange);

  },

  onRegionTicketChange : function(e) {


   this.regionTicketView( this.nodeSelectValue(this._regionTicketSelect) );


  },

  regionTicketView : function(regionValue) {

    const regionModel = this.prepareModel({

     'edit' : {

        SUB_MOSKOW : { value : 366 }

     },

     'view' : {

      SUB_MOSKOW : { value : 'Подмосковье'}

     }

    }),



    fieldsSubMoskow = ['UF_CRM_1545390183','UF_CRM_1545390196'],
    fieldsMoskow    = ['UF_CRM_1545390443','UF_CRM_1566212549228'], // округ указать UF_CRM_1566212549228
    fieldsNewMoskow = ['UF_CRM_1545390183','UF_CRM_1545390196','UF_CRM_1566212549228'];

    if(regionValue == regionModel.SUB_MOSKOW) {

      for(var uf of  fieldsMoskow) {

        this.hideField(this.node(uf));

      }

      for(var uf of  fieldsSubMoskow) {

          this.showField(this.node(uf));
      }

    } else if (regionValue == 512 || regionValue == 'Новая Москва'){
   
      for(var uf of  fieldsMoskow) {

        this.hideField(this.node(uf));

      }

      for(var uf of  fieldsNewMoskow) {

        this.showField(this.node(uf));
      }


    } else {
   
      for(var uf of  fieldsSubMoskow) {

        this.hideField(this.node(uf));

      }

      for(var uf of  fieldsMoskow) {

        this.showField(this.node(uf));
      }


    }

  },

  showTicketGeoFields : function() {

    this.regionTicketView( this.getTextValue(this.node('UF_CRM_1545390144')) );

  },


  initializeOSZEvent : function() {

    this.bindEvent(this.node('UF_CRM_1547117688'), 'click', this.onBuildTypeCheked);

  },

  onBuildTypeCheked : function(e) {

    if(e.target.nodeName == 'INPUT') {

      if(e.target.checked) {

        this.buildTypeView(e.target.value);

      } else {

        this.buildTypeView(-1);

      }

    }


  },

  buildTypeView : function(buildValue) {

    const buildTypeModel = this.prepareModel({

       'edit' :  {


          OSZ : { value : 389}

       }, 
       'view' : { 

          OSZ : { value : 'ОСЗ'}
       }

    });


    if(buildValue == buildTypeModel.OSZ) {

        this.showField(this.node('UF_CRM_1547117777330'));

    } else {

        this.hideField( this.node('UF_CRM_1547117777330'));

    }

  },

  showOSZFields : function() {
    
     this.buildTypeView(this.getEnumTextValue(this.node('UF_CRM_1547117688'), 'ОСЗ')); 

  },

  initializePayCommisionEvent : function() {

     this._commision = this.nodeSelect('UF_CRM_1547204814');

     this.bindEvent(this._commision, 'change', this.onCommisionChange);

  },

  onCommisionChange : function() {


    this.commnisionView(this.nodeSelectValue(this._commision));

  },

  commnisionView : function(commisionValue) {

    const commisionModel = this.prepareModel({

        'edit' : {

            YES : { value : 404 }

        },
        'view' : {

            YES : {value : 'Да'}

        }
     });

     if(commisionValue == commisionModel.YES) {

        this.showField(this.node('UF_CRM_1547204833'));

     } else {

        this.hideField(this.node('UF_CRM_1547204833'));

     }

  },

  showCommisionFields : function() {

 //     this.commnisionView(this.getTextValue(this.node('UF_CRM_1547204814'))); // убрали поле Платит ли комиссию

  },

  showClientContactFields : function() {


    if(this.isAdmin() || this.getCustomerID() == this.brokerAssignedID() ||
       this.brokerAssignedID() == this.getGeneralBrokerID()) {

        this.showField(this.node('UF_CRM_1547204738'));

    }


  },

  showStatusTiketFields : function() {

     if(!this.isAdmin()) {

        this.setReadOnly('UF_CRM_1547206368', this.nodeRadioLabel('UF_CRM_1547206368')); 

    }
  },

  initializeCalculateCostEvent : function() {

    this._costTypeSelect = this.nodeSelect('UF_CRM_1547218572751'); 
    this.bindEvent(this._costTypeSelect , 'change',  this.onTiketRentPriceChange);

  },

  initializeTiketRentPriceEvent : function() {

    this.bindEvent(this.nodeInput('UF_CRM_1547551210'), 'keyup', this.onTiketRentPriceChange);

  },

  onTiketRentPriceChange : function() {

    this.rentPriceView();

  },

  rentPriceView : function() {
			
    const priceSq2OnYear  = this.nodeInput('UF_CRM_1547218667182'),
          priceOnMonth    = this.nodeInput('UF_CRM_1565853455'),

    //     cashing         = this.nodeInput('UF_CRM_1547628374048'), //доходность
    //      payback         = this.nodeInput('UF_CRM_1547628348754'), //окупаемость

          priceRental     = parseFloat(this.nodeInput('UF_CRM_1547551210').value),

          costType        = parseInt(this.nodeSelectValue(this._costTypeSelect));

          NDS_SELECT      = 416,

          priceModel = this.prepareModel({

            'edit' : {

              RENT : { value : 362},
              SALE : { value : 363},
              RENT_BUSSINES : {value : 364}

            },

            'view' : {

              RENT : { value : 'Помещение в аренду'},
              SALE : { value : 'Помещение на продажу'},
              RENT_BUSSINES : { value : 'Арендный бизнес'}

            }
          });

     let  nds = 0,
          squareValue = 1; 
 
     if(this.nodeSelectValue(this.nodeSelect('UF_CRM_1547218608920')) == NDS_SELECT) {
 
        nds = (priceRental * 18) / 118;
 
     }
   
     if(this.nodeInput('UF_CRM_1547120946759')) {

        squareValue = parseInt(this.nodeInput('UF_CRM_1547120946759').value);

     } else if(this.getTextValue(this.node('UF_CRM_1547120946759'))) {

        squareValue = parseInt(this.getTextValue(this.node('UF_CRM_1547120946759')));

     }  
     
           
     if(this.getTypeBuilding() == priceModel.RENT)  {

        priceSq2OnYear.value = BX.Currency.currencyFormat(((priceRental + nds) / squareValue) * 12, 'RUB', true);

     }

     if(this.getTypeBuilding() == priceModel.SALE)  {

        priceOnMonth.value = BX.Currency.currencyFormat((priceRental + nds) / squareValue, 'RUB', true);

     }

     if(this.getTypeBuilding() == priceModel.RENT_BUSSINES)  {


      priceSq2OnYear.value = BX.Currency.currencyFormat(((priceRental + nds) / squareValue) * 12, 'RUB', true);
      priceOnMonth.value = BX.Currency.currencyFormat((priceRental + nds) / squareValue, 'RUB', true);

     }
         
     /*if(this.getTypeBuilding() == 'Арендный бизнес') {

        const priceMAP  = parseInt(this.getTextValue(this.node('UF_CRM_1547629103665')).replace(/\s+/ig,"") ) * 12;//МАП 
            
           //       paybackValue = ((priceRental + nds) / priceMAP).toFixed(1);

           //       cashing.value = (priceMAP / (priceRental + nds)) * 100 + "%";	

                  console.log(priceMAP, paybackValue);

                  payback.value = this.precentToDate(paybackValue);
                  
                  
    }*/
        
  },

  showPaybackCashingFields : function() {

    if(this.getTypeBuilding() == 'Арендный бизнес') {

    //  this.showField(this.node('UF_CRM_1547628348754'));  //окупаемость
   //   this.showField(this.node('UF_CRM_1547628374048')); // отключаем доходность в АБ

    }

  },

  initializeTicketNDSEvent : function() {

      this.bindEvent(this.nodeSelect('UF_CRM_1547218608920'), 'change',  this.onTiketRentPriceChange);

  },

  initializePlannedRunEvent : function() {

  
    this.bindEvent(this.node('UF_CRM_1547218826'), 'click', this.onPlannedRunChange);

  },

  onPlannedRunChange : function(e) {

     if(e.target.nodeName == 'INPUT') {


         this.plannedRunView(e.target.value);

     }


  },

  plannedRunView : function(plannedValue) {

    const plannedRunModel = this.prepareModel({

      'edit' : {

         FROM_DATE : { value : 421 }
      }, 

      'view' : {
 
        FROM_DATE : { value : 'С даты' }

      }

    });

    if(plannedValue == plannedRunModel.FROM_DATE) {

       this.showField(this.node('UF_CRM_1547218859'));

    } else {

      this.hideField(this.node('UF_CRM_1547218859'));

    }
  
  },

  showPlannedRunFields : function() {

   /* if(this.nodeRadio('UF_CRM_1547218826')) {

      this.plannedRunView(this.nodeRadioChecked('UF_CRM_1547218826').value);

    } else {

       this.plannedRunView(this.getTextValue(this.node('UF_CRM_1547218826')));
    }*/

  }


});