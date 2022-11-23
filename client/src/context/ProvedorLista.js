import React, {createContext, useState} from "react";

const ListaContext = createContext({});

export function ProvedorLista(props) {
    const [pagina, setPagina] = useState(1);
    const [ordenar, setOrdenar] = useState("");
    const [ordem, setOrdem] = useState("");
    const [urls, setUrls] = useState({pagina: "", backend: ""});
    const [pesquisa, setPesquisa] = useState('""');
    const [parametro, setParametro] = useState("");

    return (
        <ListaContext.Provider value={
            {
                pagina, setPagina,
                ordenar, setOrdenar,
                ordem, setOrdem,
                urls, setUrls,
                pesquisa, setPesquisa,
                parametro, setParametro
            }
        }>
            {props.children}
        </ListaContext.Provider>
    )
}

export default ListaContext;
