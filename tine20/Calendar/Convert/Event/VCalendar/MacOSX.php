<?php
/**
 * Tine 2.0
 *
 * @package     Calendar
 * @subpackage  Convert
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @copyright   Copyright (c) 2011-2013 Metaways Infosystems GmbH (http://www.metaways.de)
 */

/**
 * class to convert a Mac OS X VCALENDAR to Tine 2.0 Calendar_Model_Event and back again
 *
 * @package     Calendar
 * @subpackage  Convert
 */
class Calendar_Convert_Event_VCalendar_MacOSX extends Calendar_Convert_Event_VCalendar_Abstract
{
    // DAVKit/4.0.3 (732.2); CalendarStore/4.0.4 (997.7); iCal/4.0.4 (1395.7); Mac OS X/10.6.8 (10K549)
    // CalendarStore/5.0 (1127); iCal/5.0 (1535); Mac OS X/10.7.1 (11B26)
    // Mac OS X/10.8 (12A269) CalendarAgent/47 
    // Mac_OS_X/10.9 (13A603) CalendarAgent/174
    // Mac+OS+X/10.10 (14A389) CalendarAgent/315"
    const HEADER_MATCH = '/(?J)((CalendarStore.*Mac OS X\/(?P<version>\S+) )|(^Mac[ _+]OS[ _+]X\/(?P<version>\S+).*CalendarAgent))/';
    
    protected $_supportedFields = array(
        'seq',
        'dtend',
        'transp',
        'class',
        'description',
        #'geo',
        'location',
        'priority',
        'summary',
        'url',
        'alarms',
        #'tags',
        'dtstart',
        'exdate',
        'rrule',
        'recurid',
        'is_all_day_event',
        #'rrule_until',
        'originator_tz',
    );
    
    /**
     * get attendee array for given contact
     * 
     * @param  \Sabre\VObject\Property\ICalendar\CalAddress  $calAddress  the attendee row from the vevent object
     * @return array
     */
    protected function _getAttendee(\Sabre\VObject\Property\ICalendar\CalAddress $calAddress)
    {
        
        $newAttendee = parent::_getAttendee($calAddress);
        
        // beginning with mavericks iCal adds organiser as attedee without role
        // so we remove attendee without role 
        // @TODO check if this attendee is currentuser & organizer?
        if (version_compare($this->_version, '10.9', '>=')) {
            if (! isset($calAddress['ROLE'])) {
                return NULL;
            }
        }
        
        return $newAttendee;
    }

    /**
     * do version specific magic here
     *
     * @param \Sabre\VObject\Component\VCalendar $vcalendar
     * @return \Sabre\VObject\Component\VCalendar | null
     */
    protected function _findMainEvent(\Sabre\VObject\Component\VCalendar $vcalendar)
    {
        $return = parent::_findMainEvent($vcalendar);

        // NOTE 10.7 and 10.10 sometimes write access into calendar property
        if (isset($vcalendar->{'X-CALENDARSERVER-ACCESS'})) {
            foreach ($vcalendar->VEVENT as $vevent) {
                $vevent->{'X-CALENDARSERVER-ACCESS'} = $vcalendar->{'X-CALENDARSERVER-ACCESS'};
            }
        }

        return $return;
    }

    /**
     * parse VEVENT part of VCALENDAR
     *
     * @param  \Sabre\VObject\Component\VEvent  $vevent  the VEVENT to parse
     * @param  Calendar_Model_Event             $event   the Tine 2.0 event to update
     * @param  array                            $options
     */
    protected function _convertVevent(\Sabre\VObject\Component\VEvent $vevent, Calendar_Model_Event $event, $options)
    {

        $return = parent::_convertVevent($vevent, $event, $options);

        // NOTE: 10.7 sometimes uses (internal?) int's
        if (isset($vevent->{'X-CALENDARSERVER-ACCESS'}) && (int) (string) $vevent->{'X-CALENDARSERVER-ACCESS'} > 0) {
            $event->class = (int) (string) $vevent->{'X-CALENDARSERVER-ACCESS'} == 1 ?
                Calendar_Model_Event::CLASS_PUBLIC :
                Calendar_Model_Event::CLASS_PRIVATE;
        }

        // 10.10 sends UNTIL in wrong timezone for all day events
        if ($event->is_all_day_event && version_compare($this->_version, '10.10', '>=')) {
            $event->rrule = preg_replace_callback('/UNTIL=([\d :-]{19})(?=;?)/', function($matches) use ($vevent) {
                // refetch UNTIL from vevent and drop timepart
                preg_match('/UNTIL=([\dTZ]+)(?=;?)/', $vevent->RRULE, $matches);
                $dtUntil = Calendar_Convert_Event_VCalendar_Abstract::getUTCDateFromStringInUsertime(substr($matches[1], 0, 8));
                return 'UNTIL=' . $dtUntil->format(Tinebase_Record_Abstract::ISO8601LONG);
            }, $event->rrule);
        }
        return $return;
    }

    /**
     * iCal supports manged attachments
     *
     * @param Calendar_Model_Event          $event
     * @param Tinebase_Record_RecordSet     $attachments
     */
    protected function _manageAttachmentsFromClient($event, $attachments)
    {
        $event->attachments = $attachments;
    }
}
