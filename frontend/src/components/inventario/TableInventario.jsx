import { FaTrash, FaEdit } from "react-icons/fa";
import { formatoPesoColombiano, validarEntero } from "../../utils/formatters";

export const TableInventario = ({ items = [] }) => {

    const getNombre = (item) => item?.nombre_item_general || item.nombre || '-';
    const getCodigo = (item) => item?.codigo_item_general || item.codigo || '-';
    const getTipo = (item) => (item?.nombre_tipo || item.tipo || '-').toUpperCase();
    const getPrecio = (item) => item?.precio_venta || '-';

    const handleType = (item) => {
        const tipo = getTipo(item);
        switch (tipo) {
            case '0':
                return 'bg-blue-100 text-blue-700';
            case '1':
                return 'bg-purple-100 text-purple-700';
            case '2':
                return 'bg-yellow-100 text-yellow-700';
            default:
                return 'bg-gray-100 text-gray-700';
        }
    }

    return (
        <>
            <div className="overflow-hidden rounded-lg border mt-3 border-gray-200">
                <div className="max-h-[66vh] overflow-y-auto">
                    <table className="w-full">
                        <thead className="sticky top-0 bg-gray-700 text-white uppercase">
                            <tr>
                                <th className="px-4 py-2 text-center text-xs font-medium w-15">#</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-25">Codigo</th>
                                <th className="px-4 py-2 text-left text-xs font-medium">Nombre</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-32">Cantidad</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-32">Tipo</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-32">Unidad</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-28">Precio</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-32">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="bg-gray-100">
                            {items.map((producto, index) => (
                                <tr 
                                    key={index} 
                                    className={`border-b border-gray-300 hover:bg-gray-200 transition-colors ${
                                        index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                    }`}
                                >
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {index + 1 || '-'}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {getCodigo(producto)}
                                        </span>
                                    </td>
                                    <td className="p-1 border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {getNombre(producto)}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {validarEntero(producto.cantidad)}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className={`px-3 py-1 block text-xs font-medium rounded-md ${handleType(producto)}`}>
                                            {
                                                getTipo(producto) == '0' ? 
                                                'PRODUCTO' : getTipo(producto) == '1' ? 
                                                'MATERIA PRIMA' : getTipo(producto) == '2' ? 
                                                'INSUMO' : getTipo(producto)
                                            }
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {producto.unidad || '-'}
                                        </span>
                                    </td>
                                    <td className="p-1 text-left border border-gray-200">
                                        <span className="text-xs font-semibold text-emerald-800 pl-6">
                                            {formatoPesoColombiano(getPrecio(producto))}
                                        </span>
                                    </td>
                                    <td className="p-1 py-1 border border-gray-200">
                                        <div className="flex justify-center gap-2">
                                                <button 
                                                    className="p-2 text-white rounded-md transition-colors bg-gray-500 hover:bg-gray-800 cursor-pointer"
                                                    title="Editar"
                                                >
                                                    <FaEdit size={14} />
                                                </button>
                                                <button 
                                                    className="p-2 bg-red-500 text-white hover:bg-red-800 rounded-md transition-colors cursor-pointer"
                                                    title="Eliminar"
                                                >
                                                    <FaTrash size={14} />
                                                </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    )
}