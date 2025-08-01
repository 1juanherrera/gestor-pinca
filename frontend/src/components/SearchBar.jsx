// import { useState } from 'react';
// import { FaSearch, FaTimes } from 'react-icons/fa';

// export const SearchBar = ({ onSearch, searchTerm, onClear, placeholder = "Buscar productos..." }) => {

//     const [localSearchTerm, setLocalSearchTerm] = useState(searchTerm);

//     const handleInputChange = (e) => {
//         const value = e.target.value;
//         setLocalSearchTerm(value);
//         onSearch(value);
//     };

//     const handleClear = () => {
//         setLocalSearchTerm('');
//         onClear();
//     };

//     return (
//         <div className="relative">
//             <div className="relative">
//                 <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
//                     <FaSearch className="h-4 w-4 text-gray-400" />
//                 </div>
//                 <input
//                     type="text"
//                     value={localSearchTerm}
//                     onChange={handleInputChange}
//                     placeholder={placeholder}
//                     className="
//                         block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg 
//                         focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
//                         text-sm placeholder-gray-500
//                         transition-colors duration-200
//                     "
//                 />
//                 {localSearchTerm && (
//                     <button
//                         onClick={handleClear}
//                         className="absolute inset-y-0 right-0 pr-3 flex items-center"
//                     >
//                         <FaTimes className="h-4 w-4 text-gray-400 hover:text-gray-600 transition-colors" />
//                     </button>
//                 )}
//             </div>
//             {localSearchTerm && (
//                 <div className="absolute top-full left-0 right-0 mt-1 uppercase text-xs text-gray-500 bg-white px-3 py-1 rounded-md shadow-sm border">
//                     Buscando: "{localSearchTerm}"
//                 </div>
//             )}
//         </div>
//     );
// };