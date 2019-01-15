Object.assign( BX.Crm.EntityEditor.prototype, {
  
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
    fieldsMoskow    = ['UF_CRM_1545390443','UF_CRM_1545390372'];

    if(regionValue == regionModel.SUB_MOSKOW) {

      for(var uf of  fieldsMoskow) {

        this.hideField(this.node(uf));

      }

      for(var uf of  fieldsSubMoskow) {

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

      this.commnisionView(this.getTextValue(this.node('UF_CRM_1547204814')));

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

  initializeTiketRentPriceEvent : function() {

    this.bindEvent(this.nodeInput('UF_CRM_1547551210'), 'keyup', this.onTiketRentPriceChange);

  },

  onTiketRentPriceChange : function() {

      this.rentPriceView();

  },

  rentPriceView : function() {
			
    const priceSq2OnYear  = this.nodeInput('UF_CRM_1547218667182'),
          priceOnMonth    = this.nodeInput('UF_CRM_1547218684391'),

          priceRental     = parseFloat(this.nodeInput('UF_CRM_1547551210').value),

          squareValue     = parseInt(this.nodeInput("UF_CRM_1547551577246").value) || 1,

          NDS_SELECT      = 416;

    let   nds = 0;  
 
    if(this.nodeSelectValue(this.nodeSelect('UF_CRM_1547218608920')) == NDS_SELECT) {

          nds = (priceRental * 18) / 118;
    }

          priceSq2OnYear.value  = BX.Currency.currencyFormat(Math.round(priceRental * 12 / squareValue) + nds, 'RUB', true);  
          
          priceOnMonth.value  = BX.Currency.currencyFormat( (priceRental / squareValue) + nds, 'RUB', true);  
        
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

    if(this.getMode() === BX.Crm.EntityEditorMode.edit) {

      this.plannedRunView(this.nodeRadioChecked('UF_CRM_1547218826').value);

    } else {

       this.plannedRunView(this.getTextValue(this.node('UF_CRM_1547218826')));
    }

  }


});