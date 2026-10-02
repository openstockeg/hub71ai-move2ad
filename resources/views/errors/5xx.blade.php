@extends('errors.layout')

@section('code', $exception->getStatusCode())
@section('title', 'Something went wrong on our side')
@section('message', 'Try again in a moment. Your brief and answers are saved and will still be here.')
@section('message_ar', 'حاول مرة أخرى بعد قليل. ملخصك وإجاباتك محفوظة وستبقى هنا.')
