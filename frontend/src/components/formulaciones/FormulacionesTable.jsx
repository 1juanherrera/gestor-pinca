import { FaFlask, FaWeight, FaDollarSign } from 'react-icons/fa';
import { MdScience } from 'react-icons/md';
import { ProductSelect } from './ProductSelect';

export const FormulacionesTable = ({
    selectedProductData,
    compact = false,
    productDetail = null,
    recalculatedData,
    // loadingDetail = false,
}) => {
    if (!selectedProductData) {
        return (
            <div className="bg-white rounded-lg shadow-sm p-4 text-center">
                <div className="text-gray-400 mb-3">
                    <FaFlask size={compact ? 32 : 48} className="mx-auto" />
                </div>
                <h3 className={`${compact ? 'text-base' : 'text-lg'} font-medium text-gray-900 mb-2`}>
                    Formulaciones
                </h3>
                <p className="text-sm text-gray-500">
                    Selecciona un producto para ver sus formulaciones
                </p>
            </div>
        );
    }

    const dataToShow = recalculatedData || productDetail;

    return (
        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
            {/* Header */}
            <div className="bg-linear-to-r from-blue-500 to-blue-600 text-white px-4 py-3">
                <div className="flex items-center justify-between">
                    <div>
                        <h3 className={`${compact ? 'text-base' : 'text-lg'} font-semibold flex items-center gap-2`}>
                            <FaFlask size={compact ? 16 : 20} />
                            Formulaciones
                            
                            {ProductSelect}
                            {recalculatedData && (
                                <span className="bg-green-500 text-white text-xs px-2 py-0.5 rounded-sm">
                                    Calculado
                                </span>
                            )}
                        </h3>
                        <p className="text-blue-100 text-xs">
                            {productDetail?.item?.nombre} - {productDetail?.item?.codigo}
                        </p>
                    </div>
                    <div className="text-right">
                        <div className="text-xs text-blue-100">
                            vol: {recalculatedData ? recalculatedData?.item?.volumen_nuevo : productDetail?.item?.volumen_base || 0}
                        </div>
                        <div className="text-xs text-blue-100">
                            {productDetail?.formulaciones?.length} componentes
                        </div>
                    </div>
                </div>
            </div>

            {/* Tabla */}
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                #
                            </th>
                            <th className="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                Materia Prima
                            </th>
                            <th className="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">
                                <div className="flex items-center justify-center gap-1">
                                    <FaWeight size={10} />
                                    Cantidad
                                </div>
                            </th>
                             <th className="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">
                                <div className="flex items-center justify-center gap-1">
                                    <FaWeight size={10} />
                                    Cantidad Disp.
                                </div>
                            </th>
                            <th className="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">
                                <div className="flex items-center justify-center gap-1">
                                    <FaDollarSign size={10} />
                                    Costo Unit.
                                </div>
                            </th>
                            <th className="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider">
                                <div className="flex items-center justify-center gap-1">
                                    <FaDollarSign size={10} />
                                    Costo Total
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                    {
                        dataToShow?.formulaciones && Array.isArray(dataToShow.formulaciones) && dataToShow.formulaciones.length > 0 ? (
                            dataToShow.formulaciones.map((formulacion, index) => (
                            <tr key={formulacion.id_item_general_formulaciones || index} className="hover:bg-gray-50">
                                <td className="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                {index + 1}
                                </td>
                                {/* MATERIA PRIMA */}
                                <td className="px-3 py-2 whitespace-nowrap">
                                <div className="flex items-center">
                                    <div className="shrink-0 h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center">
                                    <MdScience className="h-3 w-3 text-blue-600" />
                                    </div>
                                    <div className="ml-3">
                                    <div className="text-xs font-medium text-gray-900">
                                        {formulacion.materia_prima_nombre || 'Sin nombre'}
                                    </div>
                                    <div className="text-xs text-gray-500">
                                        {formulacion.materia_prima_codigo || 'Sin código'}
                                    </div>
                                    </div>
                                </div>
                                </td>

                                {/* ⚖️ CANTIDAD */}
                                <td className="px-3 py-2 whitespace-nowrap text-center">
                                <div className={`text-sm font-semibold text-blue-600 ${recalculatedData ? 'text-green-600' : 'text-blue-600'}`}>
                                    {recalculatedData == null ? formulacion.cantidad : formulacion.cantidad_recalculada ?? 0}
                                    {recalculatedData && (
                                        <div className="text-xs text-gray-600 font-normal">
                                            Base: {formulacion.cantidad ?? 0}
                                        </div>
                                    )}
                                </div>
                                </td>

                                {/* 💲 COSTO UNITARIO */}
                                <td className="px-3 py-2 whitespace-nowrap text-center">
                                {
                                !recalculatedData ? (
                                    <div className={`text-sm font-semibold text-gray-600 
                                        ${formulacion.inventario_cantidad > formulacion.cantidad  ? 
                                            'text-green-600' : 'text-red-600'}`}>
                                            {formulacion.inventario_cantidad ?? 0}
                                            {/* Mostrar estado del stock */}
                                                {formulacion.inventario_cantidad < formulacion.cantidad ? (
                                                    <div className="text-xs text-red-500 font-normal">
                                                        Insuficiente
                                                </div>
                                            ) : (
                                                <div className="text-xs text-green-500 font-medium">
                                                    Suficiente
                                                </div>
                                            )}
                                    </div>
                                    )
                                    : (
                                    <div className={`text-sm font-semibold text-gray-600 
                                        ${formulacion.inventario_cantidad > formulacion.cantidad_recalculada  ? 
                                            'text-green-600' : 'text-red-600'}`}>
                                            {formulacion.inventario_cantidad ?? 0}
                                            {/* Mostrar estado del stock */}
                                                {formulacion.inventario_cantidad < formulacion.cantidad_recalculada ? (
                                                    <div className="text-xs text-red-500 font-normal">
                                                        Insuficiente
                                                </div>
                                            ) : (
                                                <div className="text-xs text-green-500 font-medium">
                                                    Suficiente
                                                </div>
                                            )}
                                    </div>
                                    )
                                }
                                </td>

                                <td className="px-3 py-2 whitespace-nowrap text-center">
                                <div className="text-sm font-semibold text-emerald-600">
                                    {formulacion.materia_prima_costo_unitario ?? 0}
                                </div>
                                </td>

                                {/* 💰 COSTO TOTAL */}
                                <td className="px-3 py-2 whitespace-nowrap text-center">
                                <div className="text-sm font-semibold text-emerald-600">
                                    {recalculatedData == null ? formulacion.costo_total_materia : formulacion.costo_total_materia_recalculado ?? 0}
                                    {recalculatedData && (
                                        <div className="text-xs text-gray-600 font-normal">
                                            Base: {formulacion.costo_total_materia ?? 0}
                                        </div>
                                    )}
                                </div>
                                </td>
                            </tr>
                            ))
                        ) : (
                            <tr>
                            <td colSpan="6" className="text-center py-4 text-gray-400">
                                No hay formulaciones disponibles.
                            </td>
                            </tr>
                        )
                        }
                    </tbody>
                </table>
            </div>

            {/* Footer */}
            <div className="bg-gray-50 px-4 py-3 border-t border-gray-400">
                <div className="flex justify-end items-center">
                    <div className="flex gap-4">
                        <div className="text-sm">
                            <span className="text-gray-600">Total Cantidad: </span>
                            <span className={`font-semibold ${recalculatedData ? 'text-green-600' : 'text-blue-600'}`}>
                                {!recalculatedData ? productDetail?.costos?.total_cantidad_materia_prima : recalculatedData?.recalculados?.total_cantidad_materia_prima}
                            </span>
                        </div>
                        <div className="text-sm">
                            <span className="text-gray-600">Total Costo: </span>
                            <span className={`font-semibold ${recalculatedData ? 'text-green-600' : 'text-emerald-600'}`}>
                                {!recalculatedData ? productDetail?.costos?.total_costo_materia_prima : recalculatedData?.recalculados?.total_costo_materia_prima}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};