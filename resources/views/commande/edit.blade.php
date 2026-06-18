@extends('layouts.app')

@section('title', 'Modifier ' . $commande->reference)
@section('page-title', 'Modifier ' . $commande->reference)

@include('commande._form')