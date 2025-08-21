import { useParams } from "react-router-dom";

export const BodegasInstalacion = () => {
  const { id } = useParams();

  // Aquí luego puedes cargar las bodegas de la instalación con id
  return (
    <div className="ml-65 p-4 bg-gray-100 min-h-screen">
      <h2 className="text-xl font-bold mb-4">Bodegas de la instalación {id}</h2>
      <table className="w-full border">
        <thead>
          <tr>
            <th className="border px-2 py-1">ID Bodega</th>
            <th className="border px-2 py-1">Nombre</th>
            <th className="border px-2 py-1">Descripción</th>
          </tr>
        </thead>
        <tbody>
          {/* Aquí irán las filas de bodegas cuando tengas datos */}
          <tr>
            <td className="border px-2 py-1" colSpan={3}>No hay bodegas registradas.</td>
          </tr>
        </tbody>
      </table>
    </div>
  );
};