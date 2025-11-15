import { FaUsers, FaBuilding, FaPhone, FaEnvelope, FaUser  } from 'react-icons/fa';

export const ClienteStats = ({ estadisticas }) => {

    const empresas = estadisticas.filter(c => Number(c.tipo) === 1);
    const particulares = estadisticas.filter(c => Number(c.tipo) === 2);
    const cantidadTelefonos = estadisticas.filter(c => c.telefono).length;

    const stats = [
        {
        name: 'Total Clientes',
        value: estadisticas?.length || 0,
        icon: FaUsers,
        color: 'blue'
        },
        {
        name: 'Empresas',
        value: empresas.length || 0,
        icon: FaBuilding,
        color: 'green'
        },
        {
        name: 'Particulares',
        value: particulares.length || 0,
        icon: FaUser,
        color: 'purple'
        },
        {
        name: 'Teléfonos',
        value: cantidadTelefonos || 0,
        icon: FaPhone,
        color: 'yellow'
        },
    ];

    const colorClasses = {
        blue: 'bg-blue-500 text-blue-100',
        green: 'bg-green-500 text-green-100',
        yellow: 'bg-yellow-500 text-yellow-100',
        purple: 'bg-purple-500 text-purple-100'
    };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-4">
      {stats.map((stat) => {
        const IconComponent = stat.icon;
        return (
          <div key={stat.name} className="bg-white rounded-lg shadow-sm p-6">
            <div className="flex items-center">
              <div className={`shrink-0 p-3 rounded-lg ${colorClasses[stat.color]}`}>
                <IconComponent size={24} />
              </div>
              <div className="ml-4">
                <p className="text-sm font-medium text-gray-600">{stat.name}</p>
                <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
};