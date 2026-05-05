<?php
// Ahmed mazen saqllah 120230723
session_start(); // i define session its like ram so the misson is save inf after Reload the page

if (empty($_SESSION['books'])) {
    $_SESSION['books'] = [
        ["ID" => 1, "Title" => "Python", "author" => "Ahmed Saqllah", "genre" => "Tech", "Year" => 1999, "Pages" => 900],
        ["ID" => 2, "Title" => "Java", "author" => "Assad Al Htabl", "genre" => "Tech", "Year" => 2020, "Pages" => 200],
        ["ID" => 3, "Title" => "Be Happey", "author" => "Rami Saleh", "genre" => "Fiction", "Year" => 2002, "Pages" => 768]
    ];
} //define asscotive array  if the session is emptey

$books = $_SESSION['books'];


function clean($data)
{ // wili dlete apace in the side and we not accept to user to write conde in web side and remove Backslashes 
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            $data[$k] = clean($v);
        }
        return $data;
    }

    $trimmed = trim($data);
    $stripped = stripslashes($trimmed);
    $cleaned  = htmlspecialchars($stripped);

    return $cleaned;
}


$genres = ["Fiction", "Non-Fiction", "Science", "History", "Biography", "Technology"]; // define genres to writhe it in optional by loop 





$errors = []; // Define array to write eroor massege
$Title = $Author = $Genre = $Year = $Pages = "";


if (!empty($_SESSION['books'])) { // if $_SESSION not Emptey the array Book will store SESSION
    $books = $_SESSION['books'];
}

function validateBook($entry, $genres, &$errors) // i creat fucntion valdtion to use it in the add and edit
{

    $data = clean($entry);


    if (empty($data['Title'])) {
        $errors['Title'] = "Title is required";
    } elseif (strlen($data['Title']) < 3 || strlen($data['Title']) > 120) { // if title less 3 or more 120 will send error massege
        $errors['Title'] = "Title must be 3 - 120 characters";
    } else {
        $Title = $data['Title']; // if not massege eroor with store it in varibles
    }

    if (empty($data['author'])) {
        $errors['author'] = "Author is required";
    } elseif (str_contains($data['author'], " ") == false) { // if  not contine space  true will send massege eror
        $errors['author'] = "You must write first and last name";
    } else {
        $Author = $data['author'];
    }

    if (empty($data['genre']) || !in_array($data['genre'], $genres)) { // if emptey and not there in the array wiil send erro massge
        $errors['genre'] = "Genre is not valid";
    } else {
        $Genre = $data['genre'];
    }

    $yearNow = date('Y'); // store the yeat now

    if (empty($data['Year'])) {
        $errors['Year'] = "Year is required";
    } elseif ($data['Year'] < 1000 || $data['Year'] > $yearNow) { // average 1000 and year now 
        $errors['Year'] = "Year must be 1000 - $yearNow";
    } else {
        $Year = $data['Year'];
    }

    if (empty($data['Pages'])) {
        $errors['Pages'] = "Pages is required";
    } elseif ($data['Pages'] <= 0) {  // if page eautls 0 or not postive wii send massege eroro
        $errors['Pages'] = "Pages must be positive";
    } else {
        $Pages = $data['Pages'];
    }

    if (!empty($errors)) { // if array erro massege not emptey will return false
        return false;
    }

    return [ // store in the array
        "Title" => $Title,
        "author" => $Author,
        "genre" => $Genre,
        "Year" => $Year,
        "Pages" => $Pages
    ];
}


if ($_SERVER['REQUEST_METHOD'] == "POST") { // if method from the form Post wii do the delete and edit and add

    if (isset($_POST['del'])) {

        $books = array_filter($books, function ($book) {
            return $_POST['del'] != $book['ID'];
        }); // any iteam do this condeion will still in array anything else will delete it

        $books = array_values($books); // will sort the iteams after delete
        $_SESSION['books'] = $books;

        header("Location: Ass2.php"); // update the page after store array Book in the seesion
        exit;
    } elseif (isset($_POST['edit'])) { // if i click the edit wii loop the i shure the id exist in the array id books  


        foreach ($books as $b) { // if exist we put all value in the input by value input in the html
            if ($b['ID'] == $_POST['edit']) {
                $Title = $b['Title'];
                $Author = $b['author'];
                $Genre = $b['genre'];
                $Year = $b['Year'];
                $Pages = $b['Pages'];
            }
        }
    } elseif (isset($_POST['save'])) { // if i clic save 

        $data = validateBook($_POST, $genres, $errors);  // will use my function 


        if ($data != false && empty($errors)) { // if the data not reutrn false mean no masege error and not emptey

            foreach ($books as $k => $value) { //will make loop for shure the id exist in the array
                if ($value['ID'] == $_POST['edit_id']) {
                    $books[$k]['Title']  = $data['Title'];
                    $books[$k]['author'] = $data['author'];
                    $books[$k]['genre']  = $data['genre'];
                    $books[$k]['Year']   = $data['Year'];
                    $books[$k]['Pages']  = $data['Pages'];
                }
            }

            $_SESSION['books'] = $books; // store the array Books them will update the page
            header("Location: Ass2.php");
            exit;
        }
    } elseif (isset($_POST['sub'])) { // if clic submit add 

        $data = validateBook($_POST, $genres, $errors);

        if ($data != false && empty($errors)) { // if the data not reutrn false mean no masege error and not emptey

            $maxID = 0;
            foreach ($books as $b) {
                if ($b['ID'] > $maxID) {
                    $maxID = $b['ID'];
                }
            }

            $countID = $maxID + 1; // even i access to the max count in the table so if biger thean them will add one

            $isFound = false;

            foreach ($books as $value) {
                if (strtolower($data['Title']) == strtolower($value['Title'])) { //I standardized the letters to avoid confusion.
                    $isFound = true;
                    break;
                }
            }


            if (!$isFound) { // i use boolean to shure the tilite not duiplcated

                array_push($books, [
                    "ID" => $countID,
                    "Title" => $data['Title'],
                    "author" => $data['author'],
                    "genre" => $data['genre'],
                    "Year" => $data['Year'],
                    "Pages" => $data['Pages']
                ]);

                $_SESSION['books'] = $books; // store the reesult them send suuccsfuuley massege
                $_SESSION['success'] = "Book Added Successfully";
            } else {
                $_SESSION['success'] = "Book already exists!"; // if false will the iteams is there exist 
            }
        } else {
            $_SESSION['errors'] = $errors; // will tore the massege erro even evrey probles come masege eroro  for private it
        }

        header("Location: Ass2.php"); // update the page
        exit;
    }
}


$success = ""; // define varible
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']); //send  success masege them delete it to not duiplcate
}
$errors = [];
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']); // /send  error masege them delete it to not duiplcate
}

$newestBook = end($books);

echo "<div class='alert alert-info'>
last Book Add: <strong>" . $newestBook['Title'] . "</strong>
</div>";

?>

<!DOCTYPE html>


<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IUG Libarary</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><!-- i concat bootstrap in css-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script><!-- i concat bootstrap in JS-->

    </link>
</head>

<body>
    <div class="container"><!--  i used container Becouse it will put the iteams in the center -->

        <h1 class="text-center my-4"> IUG Library</h1><!-- // the header will put the header in the center -->

        <div class="row gx-5">
            <div class="col-md-4">

                <h3 class="text-center">Add Book</h3>
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post">
                    <!-- i send the form for slef page and i use htmlspecialchars even i dont accept for user to write cod in my page -->


                    <div class="mb-3">

                        <label class="form-label" for="tit">Title</label>
                        <input type="text"
                            class="form-control <?php if (!empty($errors['Title'])) echo 'is-invalid'; ?>"
                            name="Title" id="tit"
                            value="<?php echo htmlspecialchars($Title); ?>" placeholder="Title : ">

                        <?php if (!empty($errors['Title'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['Title']; ?>
                            </div>
                        <?php endif;
                        ?>
                        <!--i define id in label becouse when is clik in label convert me to input them i user php in the value -->
                        <!-- even i save the input them i use form-control for give me nice style for input -->
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tit">Authoer</label>

                        <!-- i did here the same stupes define labe and read the value by php them used array error to get error in masseage  -->
                        <input type="text"
                            name="author"
                            class="form-control <?php if (!empty($errors['author'])) echo 'is-invalid'; ?>"
                            value="<?php echo $Author; ?>" placeholder="Authoer : " id="tit">

                        <?php if (!empty($errors['author'])): ?> <!-- if heppened eroor and not there erro massege i send erro massege  -->
                            <div class="invalid-feedback"> <!-- give me good style for erro massge spacficed to the tilte -->
                                <?php echo $errors['author']; ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="ge">Genre</label>

                        <select name="genre" class="form-select" id="ge">
                            <?php
                            for ($i = 0; $i < count($genres); $i++) : // i use way easiar than  old user is write php the array them put evrey iteams in option by loop

                                if ($Genre  == $genres[$i]) { //if the iteams eqaulse the Iteam selcted i will save so when i reload my page it will save selcted
                                    echo "<option value = $genres[$i] selected>" . $genres[$i] . "</option>";
                                } else {

                                    echo "<option value = $genres[$i]>" . $genres[$i] . "</option>"; // if iteam not equle it is not save after reload
                                }
                            endfor;
                            ?>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="year">Year</label>

                        <input type="number"
                            name="Year"
                            class="form-control <?php if (!empty($errors['Year'])) echo 'is-invalid'; ?>"
                            value="<?php echo $Year; ?>" placeholder="Year : " id="year">
                        <!-- print the is invalid in sytle if the eroor massge is not emptey -->

                        <?php if (!empty($errors['Year'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['Year']; ?> <!-- print the roor masege from value Yera in array errors-->

                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="pag">Pages</label>

                        <input type="number"
                            name="Pages"
                            class="form-control <?php if (!empty($errors['Pages'])) echo 'is-invalid'; ?>"
                            value="<?php echo $Pages; ?>" placeholder="Pages: " id="pag">
                        <!-- like stuep if erros massege value in the array error will print is invalid with good style form boot strap  -->

                        <?php if (!empty($errors['Pages'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['Pages']; ?>
                                <!-- if not emptey will print value for key pages in array erroes -->
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3 text-center">


                        <?php if (isset($_POST['edit'])) : ?>
                            <input type="hidden" name="edit_id" value=<?php echo $_POST['edit']; ?>>
                            <input type="submit" value="Save" name="save" class="btn btn-warning">
                            <!--i make 2 inputer the fisr for store the id and the scaound for submit -->
                        <?php else : ?>
                            <input type="submit" value="Add Book" name="sub" class="btn btn-success">
                        <?php endif; ?>
                        <!-- if click edit will hidden input add them show save input   -->

                    </div>


                </form>

            </div>

            <div class="col-md-8 ">
                <h3 class="text-center mb-3 mt-2">Books List</h3>
                <table class=" text-center table table-striped table-hover table-bordered">
                    <tr>
                        <!-- I maked set of headr them i use (th not td) becouse the Bold  -->
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>Year</th>
                        <th>Pages</th>
                        <th>Delete</th>
                        <th>Edit</th>

                    </tr>
                    <?php {
                        foreach ($books as $k => $value) :
                            // I make first loop evene i access for insted array them i make sacound loop evne i can access
                            //to iteams inside insted loop 
                            echo "<tr>";

                            foreach ($value as $keyInsted => $valueInsted) :
                                echo "<td>" . htmlspecialchars($valueInsted) . "</td>"; // i use htmlspecialchars even i dont accept to user to write cod in my page
                            // we make loop to add all inteams and buttoun in the table
                            endforeach; ?>
                            <td>
                                <button type="button"
                                    class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="<?php echo $value['ID']; ?>">Delete</button>
                            </td>

                            <td>
                                <form method="Post" action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>>

                                    <input type="hidden" name="edit" value=<?php echo $value['ID'];  ?>>
                                    <input type="submit" value="Edit" class="btn btn-primary btn-sm">
                                </form>
                            </td>



                    <?php echo "</tr>";
                        endforeach;
                    }
                    ?>
                    <h4 class="text-center">
                        Total Books
                        <span class="badge bg-primary">
                            <?php echo count($books); ?>
                        </span>
                    </h4>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure to delete the book?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="Post" id="deleteForm">
                        <input type="hidden" name="del" id="deleteID">
                        <input type="submit" value="Delete" class="btn btn-danger">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // when the modal opens get the ID from the click button and put it into the hidden input
        document.getElementById('deleteModal').addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget; // button that triggered the modal
            let id = button.getAttribute('data-id'); // get the ID from the button
            document.getElementById('deleteID').value = id; // set the ID in the hidden input
        });
    </script>

</body>

</html>