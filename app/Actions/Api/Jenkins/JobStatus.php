<?php

namespace App\Actions\Api\Jenkins;

enum JobStatus : string
{
    case NOT_EXECUTED = 'NOT_EXECUTED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case ABORTED = 'ABORTED';
    case FAILED = 'FAILED';
    case SUCCESS = 'SUCCESS';

    case NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
}
