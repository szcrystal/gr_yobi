<footer class="sticky-footer">
  <div class="container">
    <div class="text-center">
      <small></small>
    </div>
  </div>
</footer>
<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
  <i class="fa fa-angle-up"></i>
</a>
<!-- Logout Modal-->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
        <a class="btn btn-primary" href="login.html">Logout</a>
      </div>
    </div>
  </div>
</div>

    <!-- Bootstrap Core JavaScript -->
    
<?php $getNow = '' . time(); ?>	

<!-- Bootstrap core JavaScript-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    
    <script src="{{ asset('/js/app.js') }}"></script>
	
	<!--<script src="{{ asset('/js/dbd/vendor/jquery/jquery.min.js') }}"></script>-->
	
    <script src="{{ asset('/js/dbd/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Core plugin JavaScript-->
    <script src="{{ asset('/js/dbd/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <script src="{{ asset('/js/dbd/vendor/datatables/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('/js/dbd/vendor/datatables/dataTables.bootstrap4.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('/js/dbd/sb-admin.min.js') }}"></script>

    <!-- テーブルの並べ替えなどがjsで動くようになる -->
    <script src="{{ asset('/js/dbd/sb-admin-datatables.js') }}"></script>
    
    @if(Request::is('dashboard/users/*') || Request::is('dashboard/sales/order/*'))
    <script type="text/javascript" src="//jpostal-1006.appspot.com/jquery.jpostal.js"></script>
    @endif
    
	<script src="{{ asset('/js/dbd/custom.js?up=' . time()) }}"></script>


