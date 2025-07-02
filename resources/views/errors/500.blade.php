@extends('errors.generic')

@if (isset($message) && $message === 'Unauthorized access.')
    @section('errorTitle', 'Forbidden')
    @section('errorCode', '403')
    @section('errorMessage', 'You don\'t have permission to access this resource.')
@elseif(isset($message) && $message === 'Functionality not implemented yet')
    @section('errorTitle', 'Not Implemented')
    @section('errorCode', '501')
    @section('errorMessage', 'This feature is currently under development.')
@endif
