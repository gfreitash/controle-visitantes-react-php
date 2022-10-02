import React, {useRef} from "react";

import "../assets/css/foto-visitante.css";

import foto_padrao from "../assets/imgs/padrao.jpg";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faTrashCan} from "@fortawesome/free-solid-svg-icons";


export default function FotoVisitante(props) {
    const inputFotoRef = useRef();
    const fotoClienteRef = useRef();

    const [excluirFoto, setExcluirFoto] = React.useState(false);
    const [foto, setFoto] = React.useState(props.foto);

    function removerImagem() {
        fotoClienteRef.current.src = foto_padrao;
        inputFotoRef.current.value = '';
        if (props.foto && props.foto !== foto) {
            setExcluirFoto(true);
        }
    }

    function paraBase64(arquivo) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(arquivo);
            reader.onload = () => resolve(reader.result);
            reader.onerror = error => reject(error);
        });
    }

    async function handleFotoInput() {
        const inputFoto = inputFotoRef.current;
        const arquivo = inputFoto?.files[0];

        let extensoesPermitidas = /(\.jpg|\.jpeg|\.png|\.webp)$/
        if(!extensoesPermitidas.exec(inputFoto?.value)) {
            alert("Os formatos permitidos de arquivo são apenas .jpg, .jpeg, .png e .webp");
            removerImagem();
            return;
        }
        let tamanhoMaximo = 2*1024*1024; // 2MB
        if(arquivo.size > tamanhoMaximo) {
            alert("O arquivo deve ter menos de 2MB");
            removerImagem();
            return;
        }
        let base64 = await paraBase64(arquivo);
        setFoto(base64);
    }

    function handleRemoverFoto() {
        removerImagem();
    }

    return (
        <div className="form-foto" id="form-foto">
            <div className="foto-preview" id="foto-preview">
                <img alt="Foto do cliente" className="foto-cliente" src={foto ?? foto_padrao}
                     height="190px" width="100%" id="fotoCliente" ref={fotoClienteRef}/>
            </div>

            <input type="hidden" name="excluirFoto" id="excluirFoto" value={excluirFoto.toString()}/>

            {props.editavel && (
                <div className="foto-input">
                    <label htmlFor="fotoInput" className="btn btn-dark btn-sm btn-file">
                        <div id="arquivo">Escolha um arquivo</div>
                        <input readOnly className="form-file" type="file" name="fotoInput" id="fotoInput"
                               accept="image/png, image/jpg, image/jpeg, image/webp"
                               onChange={handleFotoInput} ref={inputFotoRef}/>
                    </label>
                    <div className="btn btn-dark btn-sm btn-remover" id="removerFoto" onClick={handleRemoverFoto}>
                        <FontAwesomeIcon icon={faTrashCan}/>
                    </div>
                </div>
            )}
        </div>
    )
}
