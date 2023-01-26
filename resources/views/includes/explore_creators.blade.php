<div style="margin-bottom: 30px!important; " class="mb-2">
  <h6 style="margin-bottom: 13px;" class="text-muted font-weight-light filter-explorer"><a href="https://oprivado.com/explore" style="color:#8898aa;">Perfis mais relevantes</a>
@auth
  @if ($users->total() > 3)
    <a href="javascript:void(0);" class="float-right h5 text-decoration-none refresh_creators refresh-btn mr-2">
      <i class="feather icon-refresh-cw"></i>
    </a>

    <a href="javascript:void(0);" class="float-right h5 text-decoration-none refresh_creators toggleFindFreeCreators btn-tooltip mr-3" data-toggle="tooltip" data-placement="top" title="{{ __('general.show_only_free') }}">
      <i class="feather icon-tag"></i>
    </a>
    @endif
@endauth
  </h6>
  <ul class="list-group">
    <div class="containerRefreshCreators">
      @include('includes.listing-explore-creators')
    </div><!-- containerRefreshCreators -->
  </ul>
  <a style="font-size: 12.5px;" rel="nofollow" class="text-muted" href="https://oprivado.com/creators/more-active">Ver todos<img title="Ver todos" width="12" height="12" style="height: 18px!important;margin-left: 5px;" src="https://oprivado.com/public/img/seta2.svg"> </a>
</div><!-- d-lg-none -->

