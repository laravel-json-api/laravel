<?php

namespace LaravelJsonApi\Laravel\Http\Requests;

/**
 * Enum representing different types of request methods.
 */
enum RequestMethod: string
{
    case ATTACH_RELATIONSHIP = 'AttachRelationship';
    case DETACH_RELATIONSHIP = 'DetachRelationship';
    case UPDATE_RELATIONSHIP = 'UpdateRelationship';
    case STORE = 'Create';
    case UPDATE = 'Update';
}
