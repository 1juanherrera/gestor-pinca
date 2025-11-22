import { Navigate } from "react-router-dom";

export function setToken(token) {
  if (!token) {
    console.error("El token es nulo o indefinido.");
    return;
  }

  localStorage.setItem("token", token); 
}

export function getToken() {
  return localStorage.getItem("token"); 
}

export function clearToken() {
  localStorage.removeItem("token"); 
}

export const logout = () => {
  clearToken();
  window.location.href = "/login"; 
};
