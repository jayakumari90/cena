<nav aria-label="breadcrumb">
		<ol class="breadcrumb" style="margin-top:1rem;border-radius:14.25rem;">
		  <li class="breadcrumb-item"><a href="{!! route('admin.home')!!}"> <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard</a></li>
		  @if(isset($breadcum) && is_array($breadcum) && count($breadcum)>0)
		  <?php  $br=1; $total_br=count($breadcum); ?>
		 		@foreach($breadcum as $breadcum_key=>$breadcum_url)
		 			@if($total_br == $br)
		 			 <li class="breadcrumb-item active" aria-current="page"> {!! ucfirst($breadcum_key) !!} </li>
		 			 @else
		 			 <li class="breadcrumb-item active"><a href="{!! $breadcum_url !!}"> {!! ucfirst($breadcum_key) !!}</a></li>
		 			@endif
		 			<?php $br++; ?>
		  	@endforeach
		  @endif
		</ol>
</nav>