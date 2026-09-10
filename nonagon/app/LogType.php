<?php

namespace App;

enum LogType: string
{
    case NOTE = 'note';
    case STATUS_CHANGE = 'status_change';
    case READING_LOGGED = 'reading_logged';
    case WORK_ORDER_CREATED = 'work_order_created';
    case WORK_ORDER_UPDATED = 'work_order_updated';
    case BREAKDOWN_REPORTED = 'breakdown_reported';
}
