<!-- Global Page Loader -->
<div id="page_loader"
    style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
">
    <div class="loader-container" style="text-align:center;">
        <!-- Spinner -->
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <!-- Optional text -->
        {{-- <div style="margin-top: 10px; font-size: 1rem; color: #333;">Loading...</div> --}}
    </div>
</div>
