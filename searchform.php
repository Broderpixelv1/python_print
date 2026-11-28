
<form
    role="search"
    method="get"
    class="search-form"
    action="<?php echo esc_url(home_url('/')); ?>"
>

    <input
        type="search"
        class="search-field"
        placeholder="¿Qué estás buscando?"
        aria-label="Buscar productos"
        value="<?php echo esc_attr(get_search_query(false)); ?>"
        name="s"
    />

    <input
        type="hidden"
        name="post_type"
        value="product"
    />

</form>
