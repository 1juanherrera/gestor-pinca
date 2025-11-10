import { FaTimes, FaBuilding, FaUser, FaPhone, FaEnvelope, FaMapMarkerAlt, FaIdCard } from 'react-icons/fa';

export const ItemProveedorForm = ({ itemCreate, setShowItemCreate }) => {

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div className="flex items-center justify-between p-6 border-b border-gray-200">
                    <div className="flex items-center gap-3">
                        <div className="bg-blue-100 p-2 rounded-lg">
                            <FaBuilding className="text-blue-600" size={20} />
                        </div>
                        <div>
                            <h2 className="text-xl font-semibold text-gray-900">
                                {itemCreate ? 'Editar Producto' : 'Nuevo Producto'}
                            </h2>
                            <p className="text-sm text-gray-600">
                                {itemCreate ? 'Modifica la información del producto' : 'Ingresa los datos del nuevo producto'}
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={() => setShowItemCreate(false)}
                        className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <FaTimes className="text-gray-500" size={20} />
                    </button>
                </div>
            </div>
        </div>
    )
}