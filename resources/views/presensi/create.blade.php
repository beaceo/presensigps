@extends('layouts.presensi')
@section('header')
   <!-- App Header -->
   <div class="appHeader bg-primary text-light">
    <div class="left">
        <a href="javascript:;" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">E-Presensi</div>
    <div class="right"></div>
</div>
<!-- * App Header -->
<style>
    .webcam-capture,
    .webcam-capture video {
        display: inline-block;
        width: 100% !important;
        margin: auto ;
        height: auto !important;
        border-radius: 15px;

    }

    #map { 
        height: 300px; 
    }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
crossorigin=""/>
<!-- Make sure you put this AFTER Leaflet's CSS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
crossorigin=""></script>
@endsection
@section('content')
<div class="row" style="margin-top: 70px">
    <div class="col">
        <input type="hidden" id="lokasi">
        <div class="webcam-capture"></div>
    </div>
</div>
<div class="row">
    <div class="col">
        @if ($cek > 0)
        <button id="takeabsen" class="btn-danger btn-block">
            <ion-icon name="camera"></ion-icon>
            Absen Pulang
        </button>
        @else
        <button id="takeabsen" class="btn-primary btn-block">
            <ion-icon name="camera"></ion-icon>
            Absen Masuk
        </button>
        @endif

    </div>
</div>

<div class="row-mt2">
    <div class="col">
        <div id="map"></div>
    </div>

<audio id="not_in">
        <source src="{{ asset('assets/sound/not_in.mp3') }}" type="audio/mpeg">
</audio>
<audio id="not_out">
        <source src="{{ asset('assets/sound/not_out.mp3') }}" type="audio/mpeg">
</audio>
<audio id="radius_sound">
        <source src="{{ asset('assets/sound/radius.mp3') }}" type="audio/mpeg">
</audio>
@endsection

@push('myscript')
<script>

    var not_in= document.getElementById('not_in');
    var not_out= document.getElementById('not_out');
    var radius= document.getElementById('radius_sound');
    Webcam.set({
    height: 384,  // Sesuai dengan 16:9 aspect ratio
    width: 680,   // Sesuai dengan 16:9 aspect ratio
    image_format: 'jpeg',
    jpeg_quality: 80
});


    Webcam.attach('.webcam-capture');

    var lokasi = document.getElementById('lokasi');
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(succesCallback, errorCallback);
    }
    //Ini titik merah kantor
    function succesCallback(position){
        lokasi.value = position.coords.latitude+","+position.coords.longitude;
        var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 16);
        var lokasi_kantor = "{{ $lok_kantor->lokasi_kantor }}";
        var lok = lokasi_kantor.split(","); /*javascript pake split*/
        var lat_kantor = lok[0];
        var long_kantor = lok[1];
        var radius = "{{ $lok_kantor->radius }}";
        L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
    maxZoom: 20,
    subdomains:['mt0','mt1','mt2','mt3']
    }).addTo(map);
    var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
    
    var circle = L.circle([lat_kantor, long_kantor], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.5,
        radius: radius
    }).addTo(map);
    }

    function errorCallback(){
        
    }

   $("#takeabsen").click(function(e){
    Webcam.snap(function(uri){
        image = uri;
    });
        var lokasi = $("#lokasi").val();
        $.ajax({
        type: 'POST'
        , url: '/presensi/store'
        , data: {
            _token: "{{ csrf_token() }}"
            , image: image
            , lokasi: lokasi
        }
        , cache: false
        , success: function(respond) {
            var status= respond.split("|");
                if(status[0] == "success"){
                    if(status[2]=="in"){
                        not_in.play();
                    }else{
                        not_out.play();
                    }
                   Swal.fire({
                        title: 'Berhasil!',
                        text: status[1],
                        icon: 'success'  
                    })
                    setTimeout("location.href='/dashboard'", 3000);
                }else{
                    if (status[2] == "radius"){
                        radius_sound.play();
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: status[1],
                        icon: 'error'
                    })
                        
                }
            }
        });
    });


</script>    
@endpush
