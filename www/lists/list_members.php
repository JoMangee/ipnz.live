<div class="divTable">
                    <div>
                        <div class="row">
                            <div class="col">Record No.</div>
                            <div class="col">Name</div>
                            <div class="col">Email</div>
                            <div class="col">Phone</div>
                            <div class="col">Join Type</div>
                            <div class="col">Avatar</div>
                        </div> 
                
                    <!-- <div class="divTableBody"> -->
                        <?php// $arrayNo = [0,0]; $i_r = 0; while ($results = mysqli_fetch_array($sqlSearch)) { ?>                       
                        <?php $selectedRow=0;$arrayNo = [0,0]; $i_r = 0; while ($results = $sqlSearch->fetch_assoc()) { ?>                            
                            <div class="row" <?php //echo $selectedRow == $results['No'] ? 'id="selectedRow"' : '' ?>>
                                <!-- <div class="col" id="gridNum<?php //echo $results['Record No']?>"> -->
                                    <!-- <div class="col divTableCellTag" id="gridNum<?php //echo $results['Record No']?>">
                                        <label>No</label>
                                    </div> -->
                                    <!-- <div class="col" id="gridNum<?php //echo $results['Record No']?>">
                                        <?php   echo number_format($results['No']).'.)'; $arrayNo[$i_r] = $results['No']; $i_r++; //echo 1 + $gridMax++;?>
                                    </div>
                                </div> -->
                                <div class="col">                                
                                    <!-- <div class="col divTableCellTag">                                
                                        <label>Record No</label>
                                    </div>   -->
                              
                                        <?php echo $results['No'] ?>
                                   
                                </div>             
                                <div class="col">                                
                                    <!-- <div class="col divTableCellTag">                                
                                        <label>Name</label>
                                    </div>                      -->

                                        <?php echo $results['Name'] ?>

                                </div>             
                                <div class="col">                                
                                    <!-- <div class="col divTableCellTag">                                
                                        <label>Email</label>
                                    </div>                      -->

                                        <?php echo $results['Email'] ?>

                                </div>    
                                <div class="col">                                
                                    <!-- <div class="col divTableCellTag">                                
                                        <label>Email</label>
                                    </div>                      -->

                                        <?php echo $results['Phone'] ?>

                                </div>                                   
                                <div class="col">                                
                                    <!-- <div class="col divTableCellTag">                                
                                        <label>Join Type</label>
                                    </div>                      -->

                                        <?php echo $results['Join Type']?>

                                </div>          
                                <div class="col">                                
                                    <!-- <div class="col divTableCellTag">                                
                                        <label>Avatar</label>
                                    </div>                      -->

                                        <?php echo $results['Avatar'];?>

                                </div>                                                                                                                                      
                                <?php //if ($_SESSION['access'] == 'Admin' || $_SESSION['access'] == 'Developer') { ?>
                                <div class="col">

                                </div>
                                <?php //} ?>
                            </div>
                            
                        <?php }  ?>

                    </div>
                </div>