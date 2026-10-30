
</div>
        </div>
        

        
        <footer class="footer mt-auto py-3 bg-white text-center">
            <div class="container">
                <span class="text-muted"> Copyright © <span id="year">{{ date('Y') }}</span> <a href="javascript:void(0);" class="text-dark fw-semibold">{{ env('app.title') }}</a>.
                    </a> All
                    rights
                    reserved
                </span>
            </div>
        </footer>
        

    </div>

    
<style>
    th,td{
        text-align: left !important;
    }

    .page{
       justify-content: start !important; 
    }

.myloader {
  width: 60px;
  aspect-ratio: 2;
  --_g: no-repeat radial-gradient(circle closest-side,rgb(var(--primary-rgb)) 90%,#0000);
  background: 
    var(--_g) 0%   50%,
    var(--_g) 50%  50%,
    var(--_g) 100% 50%;
  background-size: calc(100%/3) 50%;
  animation: l3 1s infinite linear;
}
@keyframes l3 {
    20%{background-position:0%   0%, 50%  50%,100%  50%}
    40%{background-position:0% 100%, 50%   0%,100%  50%}
    60%{background-position:0%  50%, 50% 100%,100%   0%}
    80%{background-position:0%  50%, 50%  50%,100% 100%}
}
</style>
    
    
    <div class="scrollToTop">
        <span class="arrow"><i class="ri-arrow-up-s-fill fs-20"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    

    @include('includes.js')
    

<script src="/assets/js/defaultmenu.min.js?v=2564"></script>   
    
   

    
    
</body>

</html>