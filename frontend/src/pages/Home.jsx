import { FaBoxOpen } from "react-icons/fa";
import { useBodegas } from "../hooks/useBodegas";

export const Home = () => {
    const { data: bodegas, isLoading, error } = useBodegas();

    console.log("Bodegas:", bodegas);

    if (isLoading) return <p>Cargando bodegas...</p>;
    if (error) return <p>Error al cargar bodegas</p>;
    if (!bodegas || bodegas.length === 0) return <p>No hay bodegas registradas</p>;

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            <div className="mb-4 flex items-center gap-2">
                <FaBoxOpen className="text-blue-500" size={25} />
                <div>
                    <h5 className="text-xl font-bold text-gray-800 mb-2 flex items-center">
                        GESTOR PINCA - PINTURAS INDUSTRIALES DEL CARIBE S.A.S
                    </h5>
                </div>
            </div>
            {bodegas.map((bodega) => (
                <button 
                    key={bodega.id_bodegas} 
                    className="p-2 border-b border-gray-200 block"
                >
                    <span>{bodega.descripcion}</span>
                </button>
            ))} 
        </div>
    );
};
