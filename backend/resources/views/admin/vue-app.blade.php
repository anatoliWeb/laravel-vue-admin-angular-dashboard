@extends('layouts.vue-admin')

@section('title', 'Vue Admin App')

{{--
    Dedicated SPA shell view for /admin/app/* routes.
    WHY:
    Keeps old Blade admin screens intact while allowing new Vue pages
    to live under an isolated URL subtree for incremental migration.
--}}

