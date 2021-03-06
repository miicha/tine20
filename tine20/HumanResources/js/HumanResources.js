/*
 * Tine 2.0
 * 
 * @package     HumanResources
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Alexander Stintzing <a.stintzing@metaways.de>
 * @copyright   Copyright (c) 2012-2013 Metaways Infosystems GmbH (http://www.metaways.de)
 *
 */

Ext.namespace('Tine.HumanResources');

/**
 * register special renderer for contract workingtime_json
 */
Tine.widgets.grid.RendererManager.register('HumanResources', 'Contract', 'workingtime_json', function(v) {
    if (! v) {
        return 0;
    }
    var object = Ext.decode(v);
    var sum = 0;
    for (var i=0; i < object.days.length; i++) {
        sum = sum + parseFloat(object.days[i]);
    }
    return sum;
});

Tine.widgets.grid.RendererManager.register('HumanResources', 'FreeTime', 'account_id', function(v) {
    if (! v) {
        return '';
    }
    
    return v.year;
});
