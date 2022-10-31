import React, {useEffect, useRef} from "react";

/**
 * Essa função serve para executar um efeito apenas uma vez, quando o componente é montado,
 * mesmo que o componente seja desmontado e montado novamente.
 *
 * O código foi adaptado de um código obtido em: https://stackoverflow.com/a/72843344/17300070
 *
 * @param effect função que será executada apenas uma vez. Espera um callback assim como React.useEffect().
 */
export default function useEffectOnce(effect) {
    const ref = useRef(true);

    useEffect(() => {
        if (ref.current) {
            ref.current = false
            return effect();
        }

        return () => {
            ref.current = false;
        }
    },[effect]);
}
