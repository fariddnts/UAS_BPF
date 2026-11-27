<section class="role-process" aria-labelledby="role-process-title">
    <div class="role-process__heading">
        <div>
            <p class="role-process__eyebrow">Alur servis</p>
            <h2 id="role-process-title">Dari booking sampai kunci kembali</h2>
        </div>
        <span>4 tahap servis</span>
    </div>

    <div class="role-process__grid">
        @foreach($workflowStages as $stage)
            <article class="role-process__card">
                <span class="role-process__number" aria-hidden="true">{{ $loop->iteration }}</span>
                <h3>{{ $stage['title'] }}</h3>
                <div class="role-process__metric">
                    <strong>{{ $stage['value'] }}</strong>
                    <span>{{ $stage['label'] }}</span>
                </div>
                <p>{{ $stage['description'] }}</p>
            </article>
        @endforeach
    </div>
</section>
