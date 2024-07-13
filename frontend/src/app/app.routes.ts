import { Routes } from '@angular/router';
import { LoginComponent } from './login/login.component';
import { Component } from '@angular/core';

export const routes: Routes = [
    { path:'login', component: LoginComponent },
    { path:'', redirectTo: 'login', pathMatch:'full' },
];
