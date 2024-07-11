import { Component } from '@angular/core';
import { AuthService } from '../auth.service';
import { Router } from 'express';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {
  loginForm: FormGroup;
  errorMessage: string = '';

  constructor(private authService: AuthService, private router: Router, private fb: FormBuilder) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required]],
    });
  }

  onSubmit(): void{
    if (this.loginForm.invalid){
      return;
    }
    const loginData = this.loginForm.value;
    this.authService.login(loginData).subscribe(
      (response) => {
        const { es_admin } = response;
        if(es_admin){
          //(<any>this.router).navigate(['/admin']);
        }else{
          //(<any>this.router).navigate.(['/'])
        }
      },
      (error) => {
        this.errorMessage = 'Usuario o contraseña incorrectos';
      }
    )
  }
}
