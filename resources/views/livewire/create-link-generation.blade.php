<div>
        <div class="modal fade" id="createLinkModal" aria-hidden="true" wire:key="create-link-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="ll-modal" role="dialog" aria-modal="true" aria-labelledby="ll-modal-title">
                        <form wire:submit.prevent="save" class="ll-modal-card">
                            <header class="ll-modal-header">
                                <h3 id="ll-modal-title">Criar link</h3>

                                <button 
                                    type="button" 
                                    class="ll-close" 
                                    wire:click="closeModal"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="ll-btn-disabled"
                                    wire:target="closeModal"
                                    aria-label="Fechar"
                                >
                                    &times;
                                </button>
                            </header>

                            <div class="ll-modal-body">
                                <label class="ll-field">
                                    <span>Nome</span>
                                    <input type="text" wire:model="Name" placeholder="Nome do link" />
                                    @error('Name') <p class="ll-error">{{ $message }}</p> @enderror
                                </label>

                                <label class="ll-field">
                                    <span>Link</span>
                                    <input type="url" wire:model="Link" placeholder="https://exemplo.com" />
                                    @error('Link') <p class="ll-error">{{ $message }}</p> @enderror
                                </label>
                            </div>

                            <footer class="ll-modal-footer">
                                <button 
                                    type="button" 
                                    class="ll-btn ll-btn-secondary"
                                    data-bs-dismiss="modal"
                                >
                                    Cancelar
                                </button>

                                <button 
                                    type="submit" 
                                    class="ll-btn ll-btn-primary"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="ll-btn-disabled"
                                    wire:target="save"
                                >
                                    <span wire:loading.remove wire:target="save">Salvar</span>
                                    <span wire:loading wire:target="save" >Salvando...</span>
                                    
                                </button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
        window.addEventListener('close-modal', () => {
            let modal = bootstrap.Modal.getInstance(document.getElementById('createLinkModal'));

            modal.hide();
        });
</script>
</div>