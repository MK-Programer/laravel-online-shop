<script type="text/javascript">
    function addToCart(id)
    {
        $.ajax({
            url: "{{ route('customer.add-to-cart') }}",
            type: 'post',
            data: {id: id},
            dataType: 'json',
            success: function(response){
                console.log(response);
                // alert(response.message);
                window.location.href = "{{ route('customer.cart') }}";
            },
            error: function(xhr, status, error){
                console.log(xhr);
                const message = xhr.responseJSON?.message || 'An unexpected error occurred.';
                alert(message);
            }
        });
    }
</script>