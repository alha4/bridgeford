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
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeCalculateCostEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeTiketRentPriceEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeTicketNDSEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeCostDoEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeSpaceDoEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeMAPDoEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showClientContactFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showStatusTiketFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializePlannedRunEvent');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showPlannedRunFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showAllFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showCommercFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'showTicketGeoFields');
    this.registerEventListener(TicketModel.ON_SEARCH, 'initializeRegionSelectEvent');


    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeCostDoEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeSpaceDoEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeMAPDoEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeBuildingTypeEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeNeedGeoEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeOSZEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializePayCommisionEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeCalculateCostEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeTiketRentPriceEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializeTicketNDSEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'showClientContactFields');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'showStatusTiketFields');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'initializePlannedRunEvent');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'showPlannedRunFields');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'showAllFields');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'showCommercFields');
    this.registerEventListener(TicketModel.ON_PERMANENT, 'showTicketGeoFields');

    if(!this._entityId) {

       this.hideFormEdit();
       this.initializeEventListener();
    }

    if(this._entityId > 0) {


      this.registerView(TicketModel.ON_SEARCH, 'showTicketGeoFields');
      this.registerView(TicketModel.ON_SEARCH, 'showRegionSelectFields');
      this.registerView(TicketModel.ON_SEARCH, 'showOSZFields');
      this.registerView(TicketModel.ON_SEARCH, 'showClientContactFields');
      this.registerView(TicketModel.ON_SEARCH, 'showPlannedRunFields');
      this.registerView(TicketModel.ON_SEARCH, 'showCommercFields');
      this.registerView(TicketModel.ON_SEARCH, 'showCostDoField');
      this.registerView(TicketModel.ON_SEARCH, 'showSpaceDoField');
      this.registerView(TicketModel.ON_SEARCH, 'showMAPDoField');

      this.registerView(TicketModel.ON_PERMANENT, 'showTicketGeoFields');
      this.registerView(TicketModel.ON_PERMANENT, 'showRegionSelectFields');
      this.registerView(TicketModel.ON_PERMANENT, 'showOSZFields');
      this.registerView(TicketModel.ON_PERMANENT, 'showClientContactFields');
      this.registerView(TicketModel.ON_PERMANENT, 'showPlannedRunFields');
      this.registerView(TicketModel.ON_PERMANENT, 'showCommercFields');
      this.registerView(TicketModel.ON_PERMANENT, 'showCostDoField');
      this.registerView(TicketModel.ON_PERMANENT, 'showSpaceDoField');
      this.registerView(TicketModel.ON_PERMANENT, 'showMAPDoField');
   
      setTimeout(() => {
 

        this.initializeViews(); 
        this.showSections();
  //      this.showAllFields();
        this.showCommercFields();

       }, 500);

   }

   setTimeout(() => {

      this.showAboutFields();

   },1000);

  },

  showAboutFields : function() {
    
  const ticketModel = this.prepareModel({
				 
      'edit' : {

        ON_OBJECT :    {value : 360},
        
      },'view' : {
     
       ON_OBJECT :    {value : 'Заявка по объекту'},
  
      }
     }),
    
     fields = ['UF_CRM_1565872302','UF_CRM_1566986090829'];


     if(this.getTicketCategoryID() == ticketModel.ON_OBJECT) {


      fields.map((code) => this.showField(this.node(code)));


     } else {

      fields.map((code) => this.hideField(this.node(code)));

    }

  },

  hideFormEdit : function() {

    const allSections = document.querySelectorAll('div[id^="section_"]'),
          viewModel = ['section_o_zayavke','section_klient'],
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

  showCommercFields : function() {  // режим просмотра карточки заявки

    const fieldsRent = ['UF_CRM_1547218667182','UF_CRM_1547218608920','UF_CRM_1547121130737','UF_CRM_1547631768634','UF_CRM_1565276254256'], //,'UF_CRM_1547218758','UF_CRM_1547218826' - убрал поля

	  fieldsSale = ['UF_CRM_1565853455'],

          fieldsRentBusiness = ['UF_CRM_1565853455','UF_CRM_1547628348754','UF_CRM_1565250284','UF_CRM_1547218608920'];

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

   const ticketModel = this.prepareModel({
				 
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
  
    switch(this.getTypeBuilding()) {

      case 'Арендный бизнес' :
      case 364 :

        if(this.getTicketCategoryID() == ticketModel.ON_SEARCH || 
           this.getTicketCategoryID() == ticketModel.ON_PERMANENT) {

           this.showField(this.nodeSection('.crm-section_arendator'));

        }

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

       //    this.showField(this.node('UF_CRM_1547631814802'));

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

    const rentBusiness = ['UF_CRM_1565853455','UF_CRM_1547628348754','UF_CRM_1565250284','UF_CRM_1547218608920'], // добавляем поля для направлений
          rent = ['UF_CRM_1547218667182','UF_CRM_1547218608920','UF_CRM_1547121130737','UF_CRM_1547631768634','UF_CRM_1565276254256','UF_CRM_1565854434'],
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


// отображение поля Стоимость ДО

  initializeCostDoEvent : function() {


    this._costDoSelect = this.nodeSelect("UF_CRM_1569479269");

    this.bindEvent(this._costDoSelect, 'change', this.onCostDoChange);

    this.showCostDoField();
    
  },

  onCostDoChange : function() {

    this.costDoView();

  },

  showCostDoField : function() {

    this.costDoView();

  },

  costDoView : function() {

    const costDoModel = this.prepareModel({

      'edit' : {
 
         SELECT_ZNACHENIE : { value : 878 },
         SELECT_DIAPAZON : { value : 879 }
 
      },
 
      'view' : {
 
        SELECT_ZNACHENIE : { value : 'точное значение'},
        SELECT_DIAPAZON : { value : 'диапазон'}
 
      }
 
    }), 
    
    selectCostDoValue = this.nodeSelectValue(this._costDoSelect) || this.getTextValue(this.node('UF_CRM_1569479269'));

    console.log(selectCostDoValue);

    if(selectCostDoValue == costDoModel.SELECT_DIAPAZON) {

       this.showField(this.node('UF_CRM_1565250601'));

    } else {

      this.hideField(this.node('UF_CRM_1565250601'));

    }
  },

// отображение поля Площадь ДО

  initializeSpaceDoEvent : function() {


    this._spaceDoSelect = this.nodeSelect("UF_CRM_1569507041");

    this.bindEvent(this._spaceDoSelect, 'change', this.onSpaceDoChange);

    this.showSpaceDoField();
    
  },

  onSpaceDoChange : function() {

    this.spaceDoView();

  },

  showSpaceDoField : function() {

    this.spaceDoView();

  },

  spaceDoView : function() {

    const spaceDoModel = this.prepareModel({

      'edit' : {
 
         SELECT_SPACE_ZNACHENIE : { value : 880 },
         SELECT_SPACE_DIAPAZON : { value : 881 }
 
      },
 
      'view' : {
 
        SELECT_SPACE_ZNACHENIE : { value : 'точное значение'},
        SELECT_SPACE_DIAPAZON : { value : 'диапазон'}
 
      }
 
    }), 
    
    selectSpaceDoValue = this.nodeSelectValue(this._spaceDoSelect) || this.getTextValue(this.node('UF_CRM_1569507041'));

    console.log(selectSpaceDoValue);

    if(selectSpaceDoValue == spaceDoModel.SELECT_SPACE_DIAPAZON) {

       this.showField(this.node('UF_CRM_1565250252'));

    } else {

      this.hideField(this.node('UF_CRM_1565250252'));

    }
  },

// отображение поля МАП ДО

  initializeMAPDoEvent : function() {


    this._mapDoSelect = this.nodeSelect("UF_CRM_1569507120");

    this.bindEvent(this._mapDoSelect, 'change', this.onMAPDoChange);

    this.showMAPDoField();
    
  },

  onMAPDoChange : function() {

    this.mapDoView();

  },

  showMAPDoField : function() {

    this.mapDoView();

  },

  mapDoView : function() {

    const mapDoModel = this.prepareModel({

      'edit' : {
 
         SELECT_MAP_ZNACHENIE : { value : 882 },
         SELECT_MAP_DIAPAZON : { value : 883 }
 
      },
 
      'view' : {
 
        SELECT_MAP_ZNACHENIE : { value : 'точное значение'},
        SELECT_MAP_DIAPAZON : { value : 'диапазон'}
 
      }
 
    }), 
    
    selectMAPDoValue = this.nodeSelectValue(this._mapDoSelect) || this.getTextValue(this.node('UF_CRM_1569507120'));

    console.log(selectMAPDoValue);

    if(selectMAPDoValue == mapDoModel.SELECT_MAP_DIAPAZON) {

       this.showField(this.node('UF_CRM_1565858762'));

    } else {

      this.hideField(this.node('UF_CRM_1565858762'));

    }
  },



  initializeRegionSelectEvent : function() {


    this._regionSelect = this.nodeSelect("UF_CRM_1569396924");

    this.bindEvent(this._regionSelect, 'change', this.onRegionSelectChange);

    this.showRegionSelectFields();
    
  },

  onRegionSelectChange : function() {

    this.regionSelectView();

  },

  regionSelectView : function() {

    const regionSelectModel = this.prepareModel({

      'edit' : {
 
         SELECT_REGION : { value : 877 },
         SELECT_ALL : { value : 876 }
 
      },
 
      'view' : {
 
        SELECT_REGION : { value : 'выбор по округам'},
        SELECT_ALL : { value : 'вся Москва'}
 
      }
 
    }), 
    
    selectValue = this.nodeSelectValue(this._regionSelect) || this.getTextValue(this.node('UF_CRM_1569396924'));

    console.log(selectValue);

    if(selectValue == regionSelectModel.SELECT_REGION) {

       this.showField(this.node('UF_CRM_1565850691'));

    } else {

      this.hideField(this.node('UF_CRM_1565850691'));

    }
  },




  showRegionSelectFields : function() {

    this.regionSelectView();

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

        SUB_MOSKOW : { value : 366 },
        NEW_MOSKOW : { value : 512 }

     },

     'view' : {

       SUB_MOSKOW : { value : 'Подмосковье'},
       NEW_MOSKOW : { value : 'Новая Москва'}

     }

    }),

    fieldsSubMoskow = ['UF_CRM_1545390183','UF_CRM_1545390196'],
    fieldsMoskow    = ['UF_CRM_1545390443','UF_CRM_1565850691','UF_CRM_1569396924'], // округ указать UF_CRM_1565850691
    fieldsNewMoskow = ['UF_CRM_1545390183','UF_CRM_1565850691','UF_CRM_1545390196','UF_CRM_1569396924'];

    if(regionValue == regionModel.SUB_MOSKOW) {

      for(var uf of  fieldsMoskow) {

        this.hideField(this.node(uf));

      }

      for(var uf of  fieldsSubMoskow) {

          this.showField(this.node(uf));
      }

    } else if (regionValue == regionModel.NEW_MOSKOW) {
   
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
    this.bindEvent(this.nodeInput('UF_CRM_1565250601'), 'keyup', this.onTiketRentPriceChange);
    this.bindEvent(this.nodeInput('UF_CRM_1547629103665'), 'keyup', this.mapView);
    this.bindEvent(this.nodeInput('UF_CRM_1565858762'), 'keyup', this.mapView);

  },

  onTiketRentPriceChange : function() {

    this.rentPriceView();

  },


  mapView : function() {

    const mapSq2OnYear    = this.nodeInput('UF_CRM_1567082450'),
          mapFrom    = parseFloat(this.nodeInput('UF_CRM_1547629103665').value),
          mapTill    = parseFloat(this.nodeInput('UF_CRM_1565858762').value);

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

     if(this.nodeInput('UF_CRM_1547120946759')) {

        squareValue = parseInt(this.nodeInput('UF_CRM_1547120946759').value);

     } else if(this.getTextValue(this.node('UF_CRM_1547120946759'))) {

        squareValue = parseInt(this.getTextValue(this.node('UF_CRM_1547120946759')));

     }  

     if(this.getTypeBuilding() == priceModel.RENT_BUSSINES)  {

        mapYearFrom = (parseInt(mapFrom / squareValue * 12)).toLocaleString('ru-RU');
        mapYearTill = (parseInt(mapTill / squareValue * 12)).toLocaleString('ru-RU');

        console.log(mapFrom, mapTill, mapYearFrom, mapYearTill);

        if (isNaN(mapFrom))   {
             
             mapYearFrom = "";
       
          }
 
        if (isNaN(mapTill))   {
             
             mapYearTill = "";
       
          }
 
         else { 

             mapYearTill = " - " + mapYearTill;

          } 


          mapSq2OnYear.value = mapYearFrom + mapYearTill + " рублей";  

     }

  },


  rentPriceView : function() {

		
    const priceSq2OnYear  = this.nodeInput('UF_CRM_1547218667182'),
          priceOnMonth    = this.nodeInput('UF_CRM_1565853455'),
          priceTill       = parseFloat(this.nodeInput('UF_CRM_1565250601').value),

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

        priceYearDo = (parseInt(((priceRental + nds) / squareValue) * 12)).toLocaleString('ru-RU');
        priceYearTill = (parseInt(((priceTill + nds) / squareValue) * 12)).toLocaleString('ru-RU');

       if (isNaN(priceTill))   {
             
             priceYearTill = "";
         
          }
 
         else { 

             priceYearTill = " - " + priceYearTill;
          } 

        priceSq2OnYear.value = priceYearDo + priceYearTill + " рублей";  //priceSq2OnYear.value = BX.Currency.currencyFormat(((priceRental + nds) / squareValue) * 12, 'RUB', true);


     }

     if(this.getTypeBuilding() == priceModel.SALE)  {

        priceYearDo = (parseInt((priceRental + nds) / squareValue)).toLocaleString('ru-RU');
        priceYearTill = (parseInt((priceTill + nds) / squareValue)).toLocaleString('ru-RU');

       if (isNaN(priceTill))   {
             
             priceYearTill = "";
         
          }
 
         else { 

             priceYearTill = " - " + priceYearTill;
          } 


        priceOnMonth.value = priceYearDo + priceYearTill + " рублей"; // priceOnMonth.value = BX.Currency.currencyFormat((priceRental + nds) / squareValue, 'RUB', true);

     }

     if(this.getTypeBuilding() == priceModel.RENT_BUSSINES)  {

        priceYearDo = (parseInt(((priceRental + nds) / squareValue) * 12)).toLocaleString('ru-RU');
        priceYearTill = (parseInt(((priceTill + nds) / squareValue) * 12)).toLocaleString('ru-RU');

        priceYearDoMonth = (parseInt((priceRental + nds) / squareValue)).toLocaleString('ru-RU');
        priceYearTillMonth = (parseInt((priceTill + nds) / squareValue)).toLocaleString('ru-RU');

        if (isNaN(priceTill))   {
             
             priceYearTill = "";
             priceYearTillMonth = "";
         
          }
 
         else { 

             priceYearTill = " - " + priceYearTill;
             priceYearTillMonth = " - " + priceYearTillMonth;

          } 


          priceSq2OnYear.value = priceYearDo + priceYearTill + " рублей";  //priceSq2OnYear.value = BX.Currency.currencyFormat(((priceRental + nds) / squareValue) * 12, 'RUB', true);
          priceOnMonth.value = priceYearDoMonth + priceYearTillMonth + " рублей";    // priceOnMonth.value = BX.Currency.currencyFormat((priceRental + nds) / squareValue, 'RUB', true);

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