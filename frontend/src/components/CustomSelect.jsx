import Select from 'react-select';

export const CustomSelect = ({ label, options, name, value, onChange, placeholder, isRequired = false }) => {
    // Estilos base que ya definimos
    const customStyles = {
        control: (base) => ({
            ...base,
            borderRadius: '0.5rem',
            borderColor: '#D1D5DB',
            fontSize: '0.875rem',
            minHeight: '38px',
        }),
        menuPortal: (base) => ({ ...base, zIndex: 9999 })
    };

    return (
        <div className="w-full">
            <label className="block text-sm font-semibold text-gray-700 mb-1">
                {label} {isRequired && <span className="text-red-700">*</span>}
            </label>
            <Select
                options={options}
                value={options.find(opt => opt.value === value)}
                onChange={(selected) => onChange({
                    target: { name, value: selected ? selected.value : '' }
                })}
                placeholder={placeholder || "Seleccione..."}
                menuPortalTarget={document.body}
                styles={customStyles}
                isSearchable={true}
                isClearable={!isRequired}
            />
        </div>
    );
};