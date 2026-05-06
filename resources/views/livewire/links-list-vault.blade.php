<div>
    <section class="vault-section">
        <div class="links-list">
            
            @php
                $Ptext = "";

                if ($vaults->isEmpty()) {
                    $Ptext = "Nenhum cofre encontrado. Crie um novo cofre para organizar seus links.";
                }
            @endphp
            
            <p class="empty-state">{{ $Ptext }}</p>
            <div wire:loading wire:loading.class="ll-btn-disabled">
                <p>Carregando...</p>
            </div>
            
            <div wire:loading.remove>

            @foreach ($vaults as $vault)
              
                <article class="board-row vault-row">
                    <div class="col-check">
                        <input type="checkbox" aria-label="Selecionar {{ $vault->name }}">
                    </div>

                    <div class="vault-info">
                        <span class="vault-icon">O</span>
                        <div>
                            <a href="#" class="vault-title">{{ $vault->name }}</a>
                            <p id="vault-link-<?php echo $vault->id; ?>">{{ $vault->link_generated }}</p>
                        </div>
                    </div>

                    <div class="col-owner">
                        <span class="owner-pill">Eu</span>
                    </div>
                    <div class="col-qrcode">
                        <button class="menu-btn" type="button" aria-label="qr code" onclick="showQRCode(`qr-code-modal-{{ $vault->id }}`)">📄</button>
                    </div>
                    <div class="col-owner">
                        <button class="menu-btn" type="button" aria-label="copiar link" onclick="copyText(`vault-link-${<?php echo $vault->id; ?>}`)">📋</button>
                    </div>
                    <div class="col-menu">
                        <button class="menu-btn" type="button" aria-label="Mais opcoes">...</button>
                    </div>
                </article>

                    <div class="qr-code-modal" id="qr-code-modal-{{ $vault->id }}" style="display: none;">
                        <?php echo generatedQRCode($vault);?>
                    </div>
            @endforeach
            </div>
            
        </div>

        @php
            use SimpleSoftwareIO\QrCode\Facades\QrCode;
            function generatedQRCode($vault) {
                
                $link = $vault->link_generated;
                return QrCode::size(200)->generate($link);
            }
        @endphp
        <script>
            
            const input = document.getElementById('search-input');
            //const preview = document.getElementById('preview');

            function copyText(elementId) {
                
                console.log(elementId);
                var copyText = document.getElementById(elementId);
                var range = document.createRange();
                range.selectNodeContents(copyText);

                var selection = window.getSelection();
                selection.removeAllRanges();
                    
                selection.addRange(range);
                document.execCommand("copy");
                alert("Texto copiado: " + copyText.innerText);
            }
            function showQRCode(elementId) {
                console.log("Element ID: ", elementId);
                qrcodeID = document.getElementById(elementId);
                if (qrcodeID.style.display === "none") {
                    qrcodeID.style.display = "block";
                } else {                    
                    qrcodeID.style.display = "none";
                }

            }
            
            input.addEventListener('input', function () {
               
                document.querySelectorAll('.vault-row').forEach(function(row) {
                    const title = row.querySelector('.vault-title').textContent.toLowerCase();
                    const searchTerm = input.value.toLowerCase();
                    if (title.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                console.log('Input value: ', input.value);
            });
        </script>
    </section>
</div>
