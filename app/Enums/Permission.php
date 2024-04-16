<?php

namespace App\Enums;

enum Permission: string
{
    case VIEW_PROFILE = 'view_profile';
    case UPDATE_PROFILE = 'update_profile';
    case CREATE_USERS = 'create_users';
    case VIEW_USERS = 'view_users';
    case UPDATE_USERS = 'update_users';
    case DELETE_USERS = 'delete_users';
    case RECEIVE_SYSTEM_ALERTS = 'receive_system_alerts';
    case VIEW_USER_ROLES = 'view_user_roles';
    case UPDATE_APP_SETTINGS = 'update_app_settings';
}
