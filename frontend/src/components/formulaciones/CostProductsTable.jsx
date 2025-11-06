import { FaDollarSign, FaFlask, FaBox, FaTag, FaPallet } from 'react-icons/fa';
import { MdWork, MdCalculate } from 'react-icons/md';
import { FaBottleWater } from "react-icons/fa6";

export const CostProductsTable = ({ 
    selectedProductData,
    productDetail = null,
    compact = false,
    recalculatedData
}) => {
    if (!selectedProductData) {
        return (
            <div className="bg-white rounded-lg shadow-sm p-4 text-center">
                <div className="text-gray-400 mb-3">
                    <MdCalculate size={48} className="mx-auto" />
                </div>
                <h3 className='text-lg font-medium text-gray-900 mb-2'>
                    Desglose de Costos
                </h3>
                <p className="text-sm text-gray-500">
                    Selecciona un producto para ver su desglose de costos
                </p>
            </div>
        )
    }

    const COST_DEFINITIONS = {
    costo_mp_galon: { label: 'COSTO MP/GALÓN', icon: <FaFlask className="text-blue-500" size={14} /> },
    costo_mg_kg: { label: 'COSTO MG/KG', icon: <FaFlask className="text-blue-500" size={14} /> },
    mod: { label: 'COSTO MOD', icon: <MdWork className="text-green-500" size={14} /> },
    envase: { label: 'ENVASE', icon: <FaBox className="text-orange-500" size={14} /> },
    etiqueta: { label: 'ETIQUETA', icon: <FaTag className="text-red-500" size={14} /> },
    bandeja: { label: 'BANDEJA', icon: <FaPallet className="text-purple-500" size={14} /> },
    plastico: { label: 'PLÁSTICO', icon: <FaBottleWater className="text-teal-500" size={14} /> },
    }

    return (
        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
            {/* Header */}
            <div className="bg-linear-to-r from-emerald-500 to-emerald-600 text-white px-4 py-3">
                <div className="flex items-center justify-between">
                    <div>
                        <h3 className={`${compact ? 'text-base' : 'text-lg'} font-semibold flex items-center gap-2`}>
                            <MdCalculate size={compact ? 16 : 20} />
                            Desglose de Costos
                            {/* {esCalculado && (
                                <span className="bg-green-500 text-white text-xs px-2 py-1 rounded-sm">
                                    Calculado
                                </span>
                            )} */}
                        </h3>
                        <p className="text-emerald-100 text-xs">
                            {productDetail?.item?.nombre}
                        </p>
                    </div>
                    <div className="text-right">
                        <div className="text-xs text-emerald-100"> 
                            Vol: {productDetail?.item?.volumen_base || 0}
                        </div>
                        <div className="text-xs text-emerald-100">
                            {productDetail?.item?.codigo}
                        </div>
                    </div>
                </div>
            </div>


            {/* Tabla */}
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Concepto
                            </th>
                            <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div className="flex items-center justify-center gap-1">
                                    <FaDollarSign size={10} />
                                    Valor
                                </div>
                            </th>  
                            <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div className="flex items-center justify-center gap-1">
                                    <FaDollarSign size={10} />
                                    Original
                                </div>
                            </th>
                        </tr>
                    </thead>
                       <tbody className="bg-white divide-y divide-gray-200">
                        {productDetail?.costos &&
                            Object.entries(productDetail.costos || {})
                            .filter(([key]) => COST_DEFINITIONS[key])
                            .map(([key, value]) => {
                                const { label, icon } = COST_DEFINITIONS[key];
                                return (
                            <tr key={key} className="hover:bg-gray-50">
                                <td className="px-3 py-2 whitespace-nowrap">
                                    <div className="flex items-center">
                                        <div className="shrink-0 mr-3">
                                        {icon}
                                        </div>
                                        <div className="text-sm font-medium text-gray-900">
                                        {label}
                                        </div>
                                    </div>
                                </td>

                                <td className="px-3 py-2 whitespace-nowrap text-center">
                                <div
                                    className={`text-sm font-semibold ${
                                    value ? 'text-emerald-600' : 'text-gray-400'
                                    }`}
                                >
                                    {value || '-'}
                                </div>
                                </td>

                                <td className="px-3 py-2 whitespace-nowrap text-center">
                                <div className="text-xs text-gray-400">
                                    {value || '-'}
                                </div>
                                </td>
                            </tr>
                            )})}
                        </tbody>

                        
                        {/* Fila del total */}
                        <tfoot>
                            <tr className="bg-gray-100 font-semibold border-t-2 border-gray-300">
                            <td className="px-3 py-3 whitespace-nowrap">
                                <div className="flex items-center">
                                    <div className="shrink-0 mr-3">
                                        <FaDollarSign className="text-emerald-600" size={16} />
                                    </div>
                                    <div className="text-sm font-bold text-gray-900">
                                        COSTO TOTAL
                                    </div>
                                </div>
                            </td>
                            <td className="px-3 py-3 whitespace-nowrap text-center">
                                <div className={`text-lg font-bold ${productDetail ? 'text-green-700' : 'text-emerald-700'}`}>
                                    {productDetail?.costos?.total || 0}
                                </div>
                            </td>
                            <td className="px-3 py-3 whitespace-nowrap text-center">
                                <div className="text-sm font-medium text-gray-600">
                                    {recalculatedData?.recalculados?.total || 0}
                                </div>
                            </td>
                        </tr>
                        </tfoot>
                </table>
            </div>

            {/* Footer */}
            <div className="bg-gray-50 px-4 py-3 border-t border-gray-400">
                <div className="flex justify-between items-center">
                    <div className="text-sm text-gray-600">
                        <span className="font-semibold">Fecha:</span> {productDetail?.costos?.fecha_calculo || 'N/A'}
                    </div>
                </div>
            </div>
        </div>
    );
};