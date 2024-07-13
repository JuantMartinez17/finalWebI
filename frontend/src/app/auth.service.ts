import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private apiUrl = '';
  constructor(private http: HttpClient) { }

  login(credentials: {email: string, password: string}): Observable<any>{
    return this.http.post<any>(`${this.apiUrl}/login`, credentials).pipe(
      tap(response => {
        localStorage.setItem('token', response.jwt);
      })
    );
  }

  logout(): void {
    localStorage.removeItem('token');
  }
  
  isLoggedIn(): boolean{
    return !!localStorage.getItem('token');
  }
  
}


