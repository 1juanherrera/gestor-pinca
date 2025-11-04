import { FaFlask, FaVial, FaPalette, FaClock, FaEye, FaTint, FaWeight, FaPaintBrush } from 'react-icons/fa';
import { MdScience } from 'react-icons/md';

export const ProductSpecificationsTable = ({ 
    selectedProductData,
    productDetail = null
}) => {
    if (!selectedProductData) {
        return (
            <div className="bg-white rounded-lg shadow-sm p-4 text-center">
                <div className="text-gray-400 mb-3">
                    <MdScience size={48} className="mx-auto" />
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-2">
                    Especificaciones Técnicas
                </h3>
                <p className="text-sm text-gray-500">
                    Selecciona un producto para ver sus especificaciones
                </p>
            </div>
        );
    }

    const PARAMETER_DEFINITIONS = {
        viscosidad: {
            label: 'VISCOSIDAD',
            icon: <FaTint className="text-blue-500" size={14} />,
            format: (v) => v || '-'
        },
        p_g: {
            label: 'P / G',
            icon: <FaWeight className="text-green-500" size={14} />,
            format: (v) => v || '-'
        },
        brillo: {
            label: 'BRILLO',
            icon: <FaEye className="text-yellow-500" size={14} />,
            format: (v) => (v === 'MATE' ? 'MATE' : v || '-')
        },
        brillo_60: {
            label: 'BRILLO 60°',
            icon: <FaEye className="text-yellow-500" size={14} />,
            format: (v) => v || '-'
        },
        molienda: {
            label: 'MOLIENDA',
            icon: <FaVial className="text-purple-500" size={14} />,
            format: (v) => (v ? `${v} H` : '-')
        },
        secado: {
            label: 'SECADO',
            icon: <FaClock className="text-orange-500" size={14} />,
            format: (v) => v || '-'
        },
        cubrimiento: {
            label: 'CUBRIMIENTO',
            icon: <FaPaintBrush className="text-indigo-500" size={14} />,
            format: (v) => v || '-'
        },
        color: {
            label: 'COLOR',
            icon: <FaPalette className="text-red-500" size={14} />,
            format: (v) => (v === 'STD' ? 'STD' : v || '-')
        },
        ph: {
            label: 'PH',
            icon: <FaFlask className="text-teal-500" size={14} />,
            format: (v) => (v === 0 ? '-' : v || '-')
        },
        poder_tintoreo: {
            label: 'PODER TINTÓREO',
            icon: <FaPalette className="text-pink-500" size={14} />,
            format: (v) => (v === 'STD' ? 'STD' : v || '-')
        },
    };

    const formatValue = (param, value) => {
        if (!value || value === 0 || value === '0') return '-';
        switch(param.toLowerCase()) {
            case 'molienda': return `${value} H`;
            case 'color': return value === 'STD' ? 'STD' : value;
            case 'poder_tintoreo': return value === 'STD' ? 'STD' : value;
            default: return value;
        }
    };

    return (
        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
            {/* Header */}
            <div className="bg-gradient-to-r from-teal-500 to-teal-600 text-white px-4 py-3">
                <div className="flex items-center justify-between">
                    <div>
                        <h3 className="text-lg font-semibold flex items-center gap-2">
                            <MdScience size={20} />
                            Especificaciones Técnicas
                        </h3>
                        <p className="text-teal-100 text-xs">
                            {selectedProductData.nombre}
                        </p>
                    </div>
                    <div className="text-right">
                        <div className="text-xs text-teal-100">
                            {productDetail?.formulaciones?.length} parámetros
                        </div>
                        <div className="text-xs text-teal-100">
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
                                Parámetro
                            </th>
                            <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Patrón
                            </th>
                            <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lote
                            </th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {Object.entries(productDetail?.item || {})
                            .filter(([key]) => PARAMETER_DEFINITIONS[key]) // solo los definidos
                            .map(([key, value]) => {
                                const { label, icon } = PARAMETER_DEFINITIONS[key];
                                return (
                                    <tr key={key} className="hover:bg-gray-50">
                                        <td className="px-3 py-2 whitespace-nowrap">
                                            <div className="flex items-center">
                                                <div className="flex-shrink-0 mr-3">
                                                    {icon}
                                                </div>
                                                <div className="text-sm font-medium text-gray-900">
                                                    {label}
                                                </div>
                                            </div>
                                        </td>

                                        <td className="px-3 py-2 whitespace-nowrap text-center">
                                            <div className="text-sm font-semibold text-teal-600">
                                                {formatValue(key, value)}
                                            </div>
                                        </td>

                                        <td className="px-3 py-2 whitespace-nowrap text-center">
                                            <div className="text-xs text-gray-500">
                                                N/A
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                    </tbody>
                </table>
            </div>

            {/* Footer */}
            <div className="bg-gray-50 px-4 py-3 border-t border-gray-400">
                <div className="flex justify-between items-center">
                    <div className="text-sm text-gray-600">
                        <span className="font-semibold">{productDetail?.parametros?.length}</span> especificaciones técnicas
                    </div>
                    <div className="text-sm text-gray-600">
                        <span className="font-semibold">Código:</span> {selectedProductData.codigo}
                    </div>
                </div>
            </div>
        </div>
    );
};