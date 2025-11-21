import { Link, useNavigate } from "react-router-dom";
import { PageTitle } from "../components/PageTitle";
import PincaLogo from "../assets/pincalogo.png";
import PincaLetters from "../assets/pincaLetters.png";
import { useLogin } from "../hooks/useLogin";
import { useState } from "react";
import { setToken } from "../store/session";
import { MiniLoader } from "../components/Loader";

export const Login = () => {

    const { login, isLoading } = useLogin();
    const navigate = useNavigate();
    
    const [credentials, setCredentials] = useState({
        username: '',
        password: '',
    })

    const handleChange = (e) => {
        const { name, value } = e.target;
        setCredentials({
            ...credentials,
            [name]: value,
        });
    }

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await login(credentials);
            console.log("Login exitoso:", response);

            if (response.ok && response.token) {
            setToken(response.token); // Guardas el token real
            navigate('/'); // Rediriges a homepage o dashboard
            } else {
            alert(response.msg || "Error de autenticación");
            }

        } catch (err) {
            console.error("Error login:", err);
            alert("Credenciales incorrectas");
        }
    }

  return (
    <div className="min-h-screen flex">
      <PageTitle title="Pinca | Login" />

      <div className="w-1/2 hidden md:flex items-center justify-center bg-black p-10">
        <img
          src={PincaLogo}
          alt="Pinca Logo"
          className="w-2/3 max-w-sm drop-shadow-2xl animate-fadeIn"
        />
      </div>

      <div className="w-full md:w-1/2 flex items-center justify-center bg-gray-100 px-6 rounded-l-[8rem]">
        <div className="w-full max-w-md bg-white rounded-2xl shadow-xl/30 p-8 space-y-6">

          <img className="w-50 mx-auto animate-fade-in" src={PincaLetters} alt="Pinca Letters" />

          <form className="space-y-5" onSubmit={handleSubmit}>
            {/* User */}
            <div>
              <label className="text-sm font-medium text-gray-600">
                Usuario
              </label>
              <input
                type="text"
                name="username"
                autoComplete="username"
                value={credentials.username}
                onChange={handleChange}
                className="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 
                           focus:outline-none focus:ring-2 focus:ring-gray-500 focus:bg-white
                           transition-all text-black"
              />
            </div>

            {/* Password */}
            <div>
              <label className="text-sm font-medium text-gray-600">
                Contraseña
              </label>
              <input
                type="password"
                name="password"
                autoComplete="current-password"
                value={credentials.password}
                onChange={handleChange}
                className="w-full mt-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-100
                           focus:outline-none focus:ring-2 focus:ring-gray-500 focus:bg-white
                           transition-all"
              />
            </div>

            {/* Submit */}
            <button
              type="submit"
              className="w-full py-3 text-white font-semibold bg-black rounded-lg 
                         hover:opacity-90 transition-all cursor-pointer shadow-md
                         hover:shadow-lg active:scale-[0.98]"
            >
              {isLoading ? <MiniLoader /> : "Ingresar"}
            </button>

            {/* Registrar */}
            <p className="text-sm text-center text-gray-600">
              ¿No tienes una cuenta?
              <Link
                to="/register"
                className="text-black font-medium hover:underline ml-1"
              >
                Registrarse
              </Link>
            </p>
          </form>
        </div>
      </div>
    </div>
  );
};
