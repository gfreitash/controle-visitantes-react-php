import React, {useEffect} from "react";
import {Form} from "react-bootstrap";
import useAxiosPrivate from "../hooks/useAxiosPrivate";

export default function RegistroVisita(props) {
    const axios = useAxiosPrivate();
    const controlador = new AbortController();

    const [form, setForm] = React.useState({
        cadastradaPor: "",
        cadastradaEm: "",
        modificadaPor: "",
        modificadaEm: "",
        finalizadaPor: "",
        finalizadaEm: "",
    });

    const grupo = {
        display: "flex",
        flexDirection: "column",
        justifyContent: "flex-end"
    }

    async function getCadastradaPor() {
        try {
            const resposta = await axios.get(`/usuario?id=${props.cadastradaPor}`, {signal: controlador.signal});
            return resposta.data.nome;
        } catch (error) {
            if (error.code === "ERR_CANCELED")
                return;
            console.log(error);
        }
    }

    async function getModificadaPor() {
        try {
            const resposta = await axios.get(`/usuario?id=${props.modificadaPor}`, {signal: controlador.signal});
            return resposta.data.nome;
        } catch (error) {
            if (error.code === "ERR_CANCELED")
                return;
            console.log(error);
        }
    }

    async function getFinalizadaPor() {
        try {
            const resposta = await axios.get(`/usuario?id=${props.finalizadaPor}`, {signal: controlador.signal});
            return resposta.data.nome;
        } catch (error) {
            if (error.code === "ERR_CANCELED")
                return;
            console.log(error);
        }
    }

    useEffect(() => {
        let isMounted = true;
        let cadastradaPor = "";
        let modificadaPor = "";
        let finalizadaPor = "";

        async function preencherCampos() {
            if (props.cadastradaEm || props.modificadaEm || props.finalizadaEm) {
                if (props.cadastradaPor) {
                    cadastradaPor = await getCadastradaPor();
                }
                if (props.modificadaPor) {
                    modificadaPor = await getModificadaPor();
                }
                if (props.finalizadaPor) {
                    finalizadaPor = await getFinalizadaPor();
                }

                setForm({
                    cadastradaPor: cadastradaPor ?? "",
                    cadastradaEm: props.cadastradaEm ?? "",
                    modificadaPor: modificadaPor ?? "",
                    modificadaEm: props.modificadaEm ?? "",
                    finalizadaPor: finalizadaPor ?? "",
                    finalizadaEm: props.finalizadaEm ?? ""
                });
            }
        }

        isMounted && preencherCampos();

        return () => {
            isMounted = false;
            controlador.abort();
        }
    }, [props.cadastradaPor, props.cadastradaEm, props.modificadaPor, props.modificadaEm, props.finalizadaPor, props.finalizadaEm]);

    return (
        <div className="form-linha">
            <div style={grupo} className="width--31_5">
                <Form.Group controlId="cadastrada-por" className="campo">
                    <Form.Label>Visita registrada por:</Form.Label>
                    <Form.Control value={form.cadastradaPor} className="form-control-sm" disabled type="text" name="cadastrada-por"/>
                </Form.Group>
                <Form.Group controlId="cadastrada-em" className="campo">
                    <Form.Label>Data da visita:</Form.Label>
                    <Form.Control value={form.cadastradaEm} className="form-control-sm" disabled type="datetime-local" name="cadastrada-em"/>
                </Form.Group>
            </div>

            <div style={grupo} className="width--31_5">
                <Form.Group controlId="modificada-por" className="campo">
                    <Form.Label>Última modificação por:</Form.Label>
                    <Form.Control value={form.modificadaPor} className="form-control-sm" disabled type="text" name="modificada-por"/>
                </Form.Group>
                <Form.Group controlId="modificada-em" className="campo">
                    <Form.Label>Última modificação em:</Form.Label>
                    <Form.Control value={form.modificadaEm} className="form-control-sm" disabled type="datetime-local" name="modificada-em"/>
                </Form.Group>
            </div>

            <div style={grupo} className="width--31_5">
                <Form.Group controlId="finalizada-por" className="campo">
                    <Form.Label>Visita finalizada por:</Form.Label>
                    <Form.Control value={form.finalizadaPor} className="form-control-sm" disabled type="text" name="finalizada-por"/>
                </Form.Group>
                <Form.Group controlId="finalizada-em" className="campo">
                    <Form.Label>Visita finalizada em:</Form.Label>
                    <Form.Control value={form.finalizadaEm} className="form-control-sm" disabled type="datetime-local" name="finalizada-em"/>
                </Form.Group>
            </div>
        </div>
    )
}
