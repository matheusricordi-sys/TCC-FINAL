// Edit funcionário modal
function editFuncionario(r) {
    openModal('modalFuncionario');
    document.getElementById('funcTitle').textContent = 'Editar funcionário';

    for (const [id, v] of [
        ['funcId', r.id],
        ['funcNome', r.nome],
        ['funcCargo', r.cargo],
        ['funcSetor', r.setor],
        ['funcMat', r.matricula],
        ['funcStatus', r.status]
    ]) {
        document.getElementById(id).value = v;
    }
}

// Edit EPI modal
function editEpi(r) {
    openModal('modalEpi');
    document.getElementById('epiTitle').textContent = 'Editar EPI';
    document.getElementById('epiId').value = r.id;
    document.getElementById('epiNome').value = r.nome;
    document.getElementById('epiCa').value = r.ca;
    document.getElementById('epiCat').value = r.categoria;
    document.getElementById('epiEst').value = r.estoque;
    document.getElementById('epiMin').value = r.estoque_minimo;

    let p = r.validade_ca.split('-');
    document.getElementById('epiVal').value = p.reverse().join('/');
}

// Edit fornecedor
function editFornecedor(r) {
    const modal = document.getElementById('modalFornecedor');
    const title = document.getElementById('supplierTitle');
    const id = document.getElementById('supplierId');
    const nome = document.getElementById('supplierName');
    const contato = document.getElementById('supplierContact');

    if (!modal || !title || !id || !nome || !contato) {
        return;
    }

    openModal('modalFornecedor');
    title.textContent = 'Editar fornecedor';
    id.value = r.id;
    nome.value = r.name ?? '';
    contato.value = r.contact ?? '';
}