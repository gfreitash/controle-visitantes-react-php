import React, {useRef} from "react";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useAuth from "../hooks/useAuth";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import {Modal} from "react-bootstrap";
import DadosVisita from "./DadosVisita";

export default function ModalVisita(props) {
    const axios = useAxiosPrivate();
    const editarFormRef = useRef();
    const handleInvalidSession = useInvalidSessionHandler();
    const {auth} = useAuth();

    const {exibirModalEditar, setExibirModalEditar} = props.editar;
    const {exibirModalFinalizar, setExibirModalFinalizar} = props.finalizar;
    const {visita, setAlerta, setStatus} = props

    const handleEditarVisita = async () => {
        try {
            const formData = new FormData(editarFormRef.current);
            formData.append("id", visita.id);
            formData.append("idUsuario", auth.id);

            const resposta = await axios.put("/visita", formData);
            if (resposta.status === 200) {
                setAlerta({tipo: "success", mensagem: "Visita editada com sucesso!"});
            }
        } catch (error) {
            console.log(error);
            switch (error.response.status) {
                case 401:
                    handleInvalidSession();
                    break;
                case 500:
                    setAlerta({tipo: "danger", mensagem: "Houve um erro interno no servidor."});
                    break;
                default:
                    setAlerta({tipo: "danger", mensagem: "Houve um erro desconhecido."});
            }
        }

        setExibirModalEditar(false);
    }

    const handleFinalizarVisita = async () => {
        try {
            const resposta = await axios.delete(`visita?id=${visita.id}&idUsuario=${auth.id}`);

            if (resposta.status === 200) {
                setAlerta({
                    tipo: "success",
                    mensagem: "Visita finalizada com sucesso!"
                });
                setStatus("fechada");
            } else {
                setAlerta({
                    tipo: "danger",
                    mensagem: `Erro ao finalizar visita!`
                })
            }
        } catch (erro) {
            switch (erro.response.status) {
                case 400:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Dados inválidos"
                    });
                    break;
                case 401:
                    handleInvalidSession();
                    break;
                case 404:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Visita não encontrada"
                    });
                    break;
                case 500:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Erro interno do servidor"
                    });
                    break;
                case 0:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Erro de conexão"
                    });
                    break;
                default:
                    setAlerta({
                        tipo: "danger",
                        mensagem: "Não foi possível finalizar a visita. Erro desconhecido"
                    });

            }
        }

        setExibirModalFinalizar(false);
    }

    const modalEditar = (
        <Modal show={exibirModalEditar} onHide={()=>setExibirModalEditar(false)} backdrop="static" keyboard={false} size="lg">
            <Modal.Header><b>Editando Visita: Nº {visita.id}</b> </Modal.Header>
            <Modal.Body>
                <form ref={editarFormRef}>
                    <DadosVisita id={visita.id}/>
                </form>
            </Modal.Body>
            <Modal.Footer>
                <button className="btn btn-secondary" onClick={()=>setExibirModalEditar(false)}>Cancelar</button>
                <button className="btn btn-primary" onClick={handleEditarVisita}>Finalizar</button>
            </Modal.Footer>
        </Modal>
    )

    const modalFinalizar = (
        <Modal show={exibirModalFinalizar} onHide={()=>setExibirModalFinalizar(false)} backdrop="static" keyboard={false}>
            <Modal.Header><b>Finalizar visita</b></Modal.Header>
            <Modal.Body>
                <p>Tem certeza que deseja finalizar essa visita de número <b>{visita.id}</b>?</p>
            </Modal.Body>
            <Modal.Footer>
                <button className="btn btn-secondary" onClick={()=>setExibirModalFinalizar(false)}>Cancelar</button>
                <button className="btn btn-primary" onClick={handleFinalizarVisita}>Finalizar</button>
            </Modal.Footer>
        </Modal>
    )

    return (
        <>
            {modalEditar}
            {modalFinalizar}
        </>
    )
}
