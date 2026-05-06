<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    @livewireStyles
</head>
<body class="dash-page">
    @php
        $pastas = [
            
            'Sem pasta' => ['link3', 'link4'],
        ];
        $modal = false;
    @endphp
    


    <div class="dash-layout">
       

        <main class="dash-main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Painel</p>
                    <h1>Todos os links</h1>
                </div>

                <div class="topbar-actions">
                    
                    <button 
                        class="create-btn" 
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#createLinkModal"
                    >
                        <span wire:loading.remove wire:target="generateLinkModal">+ Criar</span>
                        <span wire:loading wire:target="generateLinkModal">Carregando...</span>
                    </button>
                    
                    <button class="icon-btn" type="button" aria-label="Visualizacao em grade">||</button>
                    <div class="user-badge">{{ strtoupper(substr(auth()->user()->email ?? 'U', 0, 2)) }}</div>
                </div>
            </header>


            <section class="vault-board">
                
                <div class="search-Box">
                    <input class="linklist-eyebrow" id="search-input" type="text" placeholder="Filtrar links...">
                    <button class="search-Btn">🔍</button>
                    <p id="preview"></p>
                </div>
                <div class="board-head board-row">
                    <div class="col-check"></div>
                    <div class="col-name">Nome</div>
                    <div class="col-owner">Proprietario</div>
                    <div class="col-menu"></div>
                </div>
                <livewire:links-list-vault />
                
            </section>
        </main>
       
        <livewire:create-link-generation />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    @livewireScripts
</body>
</html>
