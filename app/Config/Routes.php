<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'ComplaintController::form');
$routes->get('complaints', 'ComplaintController::form');
$routes->post('complaints', 'ComplaintController::submit');
$routes->get('complaints/success/(:segment)', 'ComplaintController::success/$1');
$routes->get('complaints/track', 'TrackingController::form');
$routes->post('complaints/track', 'TrackingController::search');
$routes->get('complaints/track/(:segment)', 'TrackingController::show/$1');
$routes->get('api/participants/search', 'Api\ParticipantSearchController::index');
$routes->get('api/contingents/search', 'Api\ContingentSearchController::index');

$routes->get('admin', 'Admin\AuthController::login');
$routes->get('admin/login', 'Admin\AuthController::login');
$routes->post('admin/login', 'Admin\AuthController::attempt');
$routes->get('admin/logout', 'Admin\AuthController::logout');
$routes->get('admin/complaints', 'Admin\ComplaintAdminController::index');
$routes->get('admin/complaints/report', 'Admin\ComplaintAdminController::report');
$routes->get('admin/complaints/report/print', 'Admin\ComplaintAdminController::reportPrint');
$routes->get('admin/complaints/report/excel', 'Admin\ComplaintAdminController::reportExcel');
$routes->get('admin/complaints/export', 'Admin\ComplaintAdminController::export');
$routes->get('admin/complaints/(:num)', 'Admin\ComplaintAdminController::show/$1');
$routes->post('admin/complaints/(:num)/status', 'Admin\ComplaintAdminController::updateStatus/$1');
$routes->get('admin/events', 'Admin\EventAdminController::index');
$routes->get('admin/events/create', 'Admin\EventAdminController::create');
$routes->post('admin/events', 'Admin\EventAdminController::store');
$routes->get('admin/events/(:num)/edit', 'Admin\EventAdminController::edit/$1');
$routes->post('admin/events/(:num)', 'Admin\EventAdminController::update/$1');
$routes->get('admin/events/(:num)/sync', 'Admin\EventAdminController::sync/$1');
$routes->post('admin/events/(:num)/close-complaints', 'Admin\EventAdminController::closeComplaints/$1');
