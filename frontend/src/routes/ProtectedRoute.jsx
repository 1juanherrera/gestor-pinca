import { getToken } from "../store/session";
import { Navigate } from "react-router-dom";

const ProtectedRoute = ({ children }) => {
  const token = getToken();

  if (!token) {
    return <Navigate to="/login" />;
  }

  return children; 
};

export default ProtectedRoute;