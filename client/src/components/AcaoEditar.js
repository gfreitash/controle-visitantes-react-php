import React, {useState} from "react";

export default function AcaoEditar(props) {
    const [editando, setEditando] = useState(false);

    let btn__margin = {
        margin: "0 0.2rem"
    }

    const botaoEditar = () => {
        return (
            <button type="button" className="btn btn-dark" onClick={onClickEditar}>Editar</button>
        );
    }

    const botoesCancelarSalvar = () => {
        return (
            <div>
                <button type="button" className="btn btn-secondary" style={btn__margin} onClick={onClickCancelar}>
                    Cancelar
                </button>
                <button type="button" className="btn btn-success" style={btn__margin} onClick={onClickSalvar}>
                    Salvar
                </button>
            </div>
        );
    }

    const onClickEditar = () => {
        if(props.onClickEditar) {
            props.onClickEditar();
        }
        setEditando(true);
    }

    const onClickCancelar = () => {
        if(props.onClickCancelar) {
            props.onClickCancelar();
        }
        setEditando(false);
    }

    const onClickSalvar = () => {
        if(props.onClickSalvar) {
            props.onClickSalvar();
        }
        setEditando(false);
    }

    return (
        <>
            {editando ? botoesCancelarSalvar() : botaoEditar()}
        </>
    )
}
