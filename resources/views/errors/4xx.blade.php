@extends('errors.layout')

@section('code', $exception->getStatusCode())
@section('title', 'This request could not be completed')
@section('message', 'Go back and try again. If you submitted a form, reload the page first.')
@section('message_ar', 'ارجع وحاول مرة أخرى. إذا أرسلت نموذجًا، أعد تحميل الصفحة أولًا.')
