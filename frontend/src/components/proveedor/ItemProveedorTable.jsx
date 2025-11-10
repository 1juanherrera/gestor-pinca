import { FaBox, FaEdit, FaTrash } from 'react-icons/fa';

export const ItemProveedorTable = ({ itemProveedor, itemId, selected = false, onToggleSelect = () => {} }) => {

    return (
        <tr className="border-b border-gray-300 hover:bg-gray-200 transition-colors">
            <td className="px-2 py-2 text-center border border-gray-200">
                <input
                    type="checkbox"
                    className="w-4 h-4 mx-auto"
                    checked={selected}
                    onChange={() => onToggleSelect(itemId)}
                />
            </td>
            <td className="px-2 py-2 text-xs font-medium text-gray-900 border border-gray-200 max-w-40 whitespace-nowrap overflow-hidden text-ellipsis">
                <div className="flex items-center gap-2 min-w-0">
                    <FaBox className="text-green-600 shrink-0" />
                    <span className="truncate block uppercase">{itemProveedor.nombre}</span>
                </div>
            </td>
            <td className="px-2 py-2 text-xs text-gray-900 border border-gray-200 max-w-[100px] whitespace-nowrap overflow-hidden text-ellipsis">
                <span className="truncate block font-semibold">{itemProveedor.codigo}</span>
            </td>
            <td className="px-2 py-2 text-xs text-gray-900 border border-gray-200 max-w-70 whitespace-nowrap overflow-hidden text-ellipsis">
                <div className="flex items-center gap-2 min-w-0">
                    <span className="truncate block uppercase font-semibold">
                        {itemProveedor.nombre_empresa || <span className="text-gray-400">Sin empresa</span>}
                    </span>
                </div>
            </td>
            <td className="px-2 py-2 text-xs font-semibold text-left text-emerald-800 border border-gray-200 max-w-[110px] whitespace-nowrap overflow-hidden text-ellipsis">
                <p className='ml-4'>{itemProveedor.precio_unitario}</p>
            </td>
            <td className="px-2 py-2 text-xs font-semibold text-emerald-800 border border-gray-200 max-w-[110px] whitespace-nowrap overflow-hidden text-ellipsis text-left">
                <p className='ml-4'>{itemProveedor.precio_con_iva}</p>
            </td>
            <td className="px-2 py-2 text-xs text-gray-900 text-center border border-gray-200 max-w-20 whitespace-nowrap overflow-hidden text-ellipsis">
                <span className="truncate block uppercase">
                    {itemProveedor.unidad_empaque || <span className="text-gray-400">No especificada</span>}
                </span>
            </td>
            <td className="px-2 py-1 border border-gray-200 min-w-[110px]">
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
    );
};