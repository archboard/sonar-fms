<?php

use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;
use Spatie\Multitenancy\Actions\MigrateTenantAction;
use Spatie\Multitenancy\Models\Tenant;

return [
    /*
     * This class is responsible for determining which tenant should be current
     * for the given request.
     *
     * This class should extend `Spatie\Multitenancy\TenantFinder\TenantFinder`
     *
     */
    'tenant_finder' => \Spatie\Multitenancy\TenantFinder\DomainTenantFinder::class,

    /*
     * These fields are used by tenant:artisan command to match one or more tenant
     */
    'tenant_artisan_search_fields' => [
        'id',
    ],

    /*
     * These tasks will be performed when switching tenants.
     *
     * A valid task is any class that implements Spatie\Multitenancy\Tasks\SwitchTenantTask
     */
    'switch_tenant_tasks' => [
        \Spatie\Multitenancy\Tasks\PrefixCacheTask::class,
        \App\Tasks\ChangeConfigTask::class,
        // I think scope needs to be set to school, not tenant
        // since schools are the lowest 'tenancy' in the app
        // and permissions are restricted to them
        // \App\Tasks\ScopeBouncer::class,
    ],

    /*
     * This class is the model used for storing configuration on tenants.
     *
     * It must be or extend `Spatie\Multitenancy\Models\Tenant::class`
     */
    'tenant_model' => \App\Models\Tenant::class,

    /*
     * If there is a current tenant when dispatching a job, the id of the current tenant
     * will be automatically set on the job. When the job is executed, the set
     * tenant on the job will be made current.
     */
    'queues_are_tenant_aware_by_default' => true,

    /*
     * The connection name to reach the tenant database.
     *
     * Set to `null` to use the default connection.
     */
    'tenant_database_connection_name' => null,

    /*
     * The connection name to reach the landlord database
     */
    'landlord_database_connection_name' => null,

    /*
     * This key will be used to bind the current tenant in the container.
     */
    'current_tenant_container_key' => 'currentTenant',

    /*
     * You can customize some of the behavior of this package by using our own custom action.
     * Your custom action should always extend the default one.
     */
    'actions' => [
        'make_tenant_current_action' => MakeTenantCurrentAction::class,
        'forget_current_tenant_action' => ForgetCurrentTenantAction::class,
        'make_queue_tenant_aware_action' => MakeQueueTenantAwareAction::class,
        'migrate_tenant' => MigrateTenantAction::class,
    ],

    /*
     * You can customize the way in which the package resolves the queuable to a job.
     *
     * For example, using the package laravel-actions (by Loris Leiva), you can
     * resolve JobDecorator to getAction() like so: JobDecorator::class => 'getAction'
     */
    'queueable_to_job' => [
        SendQueuedMailable::class => 'mailable',
        SendQueuedNotifications::class => 'notification',
        CallQueuedListener::class => 'class',
        BroadcastEvent::class => 'event',
    ],
];
