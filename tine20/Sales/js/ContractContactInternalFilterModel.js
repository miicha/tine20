/**
 * Tine 2.0
 * 
 * @package     Sales
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Alexander Stintzing <a.stintzing@metaways.de>
 * @copyright   Copyright (c) 2014 Metaways Infosystems GmbH (http://www.metaways.de)
 */
 
Ext.ns('Tine.Sales');

/**
 * @namespace   Tine.Sales
 * @class       Tine.Sales.ContractContactInternalFilterModel
 * @extends     Tine.widgets.grid.ForeignRecordFilter
 * 
 * @author      Alexander Stintzing <a.stintzing@metaways.de>
 */
Tine.Sales.ContractContactInternalFilterModel = Ext.extend(Tine.widgets.grid.ForeignRecordFilter, {
    
    // private
    field: 'costcenter',
    valueType: 'relation',
    
    /**
     * @private
     */
    initComponent: function() {
        this.app = Tine.Tinebase.appMgr.get('Sales');
        this.label = this.app.i18n._('Contact Person (internal)');
        this.foreignRecordClass = Tine.Addressbook.Model.Contact;
        this.pickerConfig = {emptyText: this.app.i18n._('without internal contact person'), allowBlank: true};

        Tine.Sales.ContractContactInternalFilterModel.superclass.initComponent.call(this);
    }
});

Tine.widgets.grid.FilterToolbar.FILTERS['sales.contract-contact-internal'] = Tine.Sales.ContractContactInternalFilterModel;
