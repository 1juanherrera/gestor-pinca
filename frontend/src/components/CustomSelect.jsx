import Select from 'react-select';

export const CustomSelect = ({ label, options, name, value, onChange, placeholder, error, isRequired = false }) => {

    const customStyles = {
        control: (base) => ({
            ...base,
            borderRadius: '0.5rem',
            borderColor: '#D1D5DB',
            fontSize: '0.875rem',
            minHeight: '38px',
            '&:hover': { borderColor: '#9CA3AF' }
        }),
        menuPortal: (base) => ({ ...base, zIndex: 9999 })
    };


    const selectedValue = options.find(opt => {
        const val1 = String(opt.value ?? '');
        const val2 = String(value ?? '');
        return val1 === val2;
    }) || null;

    return (
        <div className="w-full">
            <label className='block text-sm font-semibold mb-1 text-gray-700'>
                {label} {isRequired && <span className="text-red-700">*</span>}
            </label>
            <Select
                options={options}
                value={selectedValue}
                onChange={(selected) => onChange({
                    target: { name, value: selected ? selected.value : '' }
                })}
                placeholder={placeholder || "SELECCIONE..."}
                menuPortalTarget={document.body}
                styles={customStyles}
                isSearchable={true}
                isClearable={!isRequired}
                noOptionsMessage={() => "No hay resultados"}
                loadingMessage={() => "Cargando..."}
            />

            {error && <p className="text-red-500 text-[11px] font-bold mt-1 uppercase">{error}</p>}
        </div>
    );
};