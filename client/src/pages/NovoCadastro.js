import React, {useRef, useState} from "react";
import {Link, useNavigate} from "react-router-dom";

import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import useAuth from "../hooks/useAuth";

import "../assets/css/form-cadastro.css"
import {validaCPF} from "../assets/js/modules/dados-visitante";

import Titulo from "../components/Titulo";
import FotoVisitante from "../components/FotoVisitante";
import DadosVisitante from "../components/DadosVisitante";

export default function NovoCadastro() {
    const axios = useAxiosPrivate();
    const navigate = useNavigate();
    const {auth} = useAuth();
    const handleInvalidSession = useInvalidSessionHandler();

    const alertaRef = useRef();

    const [visitanteEncontrado, setVisitanteEncontrado] = useState({});
    const [buscaRealizada, setBuscaRealizada] = useState(false);
    const [alerta, setAlerta] = useState({tipo: "", mensagem: ""});

    const onCpfValido = (codigoResposta, visitante) => {
        setVisitanteEncontrado(visitante);
        setBuscaRealizada(true);
        setAlerta({tipo:"", mensagem: ""});
    };

    const editarVisitante = (
        <Link to={`/visitante?cpf=${visitanteEncontrado.cpf}`}>
            <button type="button" className="btn btn-success btn-acao">
                Detalhes
            </button>
        </Link>

    )

    async function handleSubmit(event) {
        event.preventDefault();
        if(!validaCPF(event.target.cpf, ()=>{})) {
            return;
        }

        try {
            const formData = new FormData(event.target);
            formData.append("idUsuario", auth.id);
            const response = await axios.post("/visitante", formData);

            if (response.status === 201 || response.status === 200) {
                const cpf = response.data.cpf;
                navigate(`/visitante?cpf=${cpf}`);
            }
        } catch (error) {
            switch (error.response.status) {
                case 400:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível cadastrar o visitante. Verifique os dados e tente novamente."});
                    break;
                case 401:
                    handleInvalidSession();
                    break;
                case 409:
                    setAlerta({tipo: "danger", mensagem: "Já existe um visitante cadastrado com esse CPF."});
                    break;
                case 500:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível cadastrar o visitante. Houve um erro interno no servidor."});
                    break;
                case 0:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível cadastrar o visitante. O servidor está inacessível."});
                    break;
                default:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível cadastrar o visitante. Houve um erro desconhecido."});
            }
        }
    }

    return (
        <div className="form-fieldset">
            <Titulo conteudoTitulo={buscaRealizada && visitanteEncontrado ? editarVisitante : ""}>Novo Cadastro</Titulo>
            <hr/>
            <div id="alerta" ref={alertaRef} className={alerta.mensagem ? `alert alert-${alerta.tipo}` : ""}>{alerta.mensagem}</div>
            <form className="needs-validation" id='formContato' encType='multipart/form-data' noValidate
            onSubmit={handleSubmit}>
                <section className="form">
                    <FotoVisitante editavel={buscaRealizada && !visitanteEncontrado}
                                   foto={visitanteEncontrado.id ? visitanteEncontrado.foto : ""}/>

                    <div className="form-wrapper">
                        <div className="width--95">
                            <DadosVisitante estado={!buscaRealizada || visitanteEncontrado ? "disabled" : ""}
                                            cpfAutoFocus onCpfValido={onCpfValido} buscarDados={true}/>
                        </div>
                    </div>
                </section>

                <div className="acao">
                    <Link to="/inicio">
                        <button type="button" id="botao-1" className="btn btn-secondary btn-acao">
                            Cancelar
                        </button>
                    </Link>
                    <button type="submit" id="botao-2" className="btn btn-primary btn-acao"
                            disabled={!buscaRealizada || visitanteEncontrado}>
                        Cadastrar
                    </button>
                </div>
            </form>
        </div>
    )
}
