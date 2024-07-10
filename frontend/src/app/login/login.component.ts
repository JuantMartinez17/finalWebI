import { Component } from '@angular/core';
import { AuthService } from '../auth.service';
import { Router } from 'express';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {
  email: string = '';
  password: string = '';

  constructor(private authService: AuthService, private router: Router) { }

  onSubmit(): void{
    this.authService.login(this.email, this.password).subscribe(response => {
      if(response.success){
        (<any>this.router).navigate(['/admin']);
      }else{
        alert('login failed');
      }
    });
  }

}
