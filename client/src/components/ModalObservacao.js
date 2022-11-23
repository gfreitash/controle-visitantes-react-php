import React, {useEffect, useState} from "react";
import {Form, Modal} from "react-bootstrap";
import useAuth from "../hooks/useAuth";
import useAxiosPrivate from "../hooks/useAxiosPrivate";

export default function ModalObservacao({exibir, onFechar, onSucesso, onFalha, modo, id, idVisita, observacao}) {
    const {auth} = useAuth();
    const axios = useAxiosPrivate()

    const [novaObservacao, setNovaObservacao] = useState(observacao ?? "");

    const url = "/observacao";

    const limparForm = (sucesso) => {
        if (modo === "editar") {
            if (sucesso !== true) {
                setNovaObservacao(observacao);
            }
        } else {
            setNovaObservacao("");
        }
    }

    const handleFechar = (sucesso=false) => {
        onFechar();
        limparForm(sucesso);
    }

    const handleSalvar = async () => {
        const formData = new FormData();
        formData.append("observacao", novaObservacao);
        formData.append("idUsuario", auth.id);
        formData.append("idVisita", idVisita);

        let metodo;
        if (modo === "editar") {
            metodo = "PUT";
            formData.append("id", id);
        } else {
            metodo = "POST";
        }

        try {
            const resposta = await axios.request({
                method: metodo,
                url: url,
                data: formData
            })

            if (resposta.status === 200 || resposta.status === 201) {
                onSucesso(resposta.data);
                handleFechar(true)
            }
        } catch (e) {
            console.log(e);
            onFalha(e);
            handleFechar()
        }
    }

    useEffect(() => {
        setNovaObservacao(observacao ?? "");
    }, [observacao]);

    if (modo !== "editar" && modo !== "adicionar") {
        return ("");
    }

    return (
        <Modal
            show={exibir}
            onHide={handleFechar}
            backdrop="static"
            keyboard={false}
        >
            <Modal.Header closeButton>
                <Modal.Title>
                    {modo === "editar"
                        ? (<>Editar observação de <b>ID {id}</b></>)
                        : "Adicionar nova observação"
                    }
                </Modal.Title>
            </Modal.Header>

            <Modal.Body>
                <Form>
                    <Form.Group controlId="observacao">
                        <Form.Label>Observação:</Form.Label>
                        <Form.Control as="textarea" rows={4} value={novaObservacao} name="observacao"
                                      onChange={(e)=>setNovaObservacao(e.target.value)}/>
                    </Form.Group>
                </Form>
            </Modal.Body>

            <Modal.Footer>
                <button className="btn btn-secondary" onClick={handleFechar}>Fechar</button>
                <button className="btn btn-primary" onClick={handleSalvar}>Salvar</button>
            </Modal.Footer>
        </Modal>
    )
}
