
<?php include 'partials/_head.php' ?>

    <div style="height: auto; background-color: #f1f1f1; " class="dashboard">
        <div style="position: sticky; top: 0; z-index: 5">
            <?php include 'partials/_navbar.php' ?>
        </div>
        
        <div style="display: grid; grid-template-columns: 250px 1fr">
            <?php include 'partials/_sidebar.php' ?>
            <div class="py-3 pe-3 ps-5">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb" class="mt-3 breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item" style="font-size: 14px;">Subject</li>
                        <li class="breadcrumb-item" style="font-size: 14px;"><a href="teachers.php" class="text-decoration-none text-dark">Edit</a></li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fs-4 mt-2">Subject</div>
                </div>

                <div id="screenLoaderCon" style="height: 80%; display: flex" class="flex-column gap-1 justify-content-center align-items-center">
                    <svg id="screenLader" viewBox="25 25 50 50">
                        <circle r="20" cy="50" cx="50"></circle>
                    </svg>
                    <div class="text-secondary">Loading...</div>
                </div>

                <div id="content" style="display: none;">
                    <div class=" d-flex flex-column gap-4 bg-white p-4 shadow rounded-4 mt-4">
                        <div class=" text-secondary">Details</div>
                        <form action="" class="d-flex flex-column gap-5 mb-3">
                            <div class="d-flex align-items gap-5">
                                <div class="input-group">
                                    <label for="subjectName" class="text-dark">Name</label>
                                    <input type="text" name="subjectName" id="subjectName" style="width: 100%;" placeholder="">
                                </div>
                                <div class="input-group">
                                    <label for="sy" class="text-dark">SY</label>
                                    <input type="text" value="2025-2026" disabled name="sy" id="sy" style="width: 100%;" placeholder="">
                                </div>
                                <div class="input-group">
                                    <label for="level" class="text-dark">Year Level</label>
                                    <select class="" name="level" id="level" style="border: none; box-shadow: none; border-bottom: 1px solid #808b96; outline: none !important; width: 100%">
                                        <option value="" class="text-secondary" selected disabled>Select level</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-5">
                                <div class="input-group d-flex flex-column align-items-baseline justify-content-between" style=" width: 47%">
                                    <label for="sem" class="text-dark">Semester</label>
                                    <select class="" name="sem" id="sem" style="border: none; box-shadow: none; border-bottom: 1px solid #808b96; outline: none !important; width: 100%">
                                        <option value="" class="text-secondary" selected disabled>Select semester</option>
                                        <option value="1st Semester">1st Semester</option>
                                        <option value="2nd Semester">2nd Semester</option>
                                    </select>                                
                                </div>
                                <div class="d-flex flex-column input-group">
                                    <label for="level" class="text-dark">Teacher</label>
                                    <select id="teacherSelect" name="teachers[]" class="form-select" style="border: none;" multiple>
                                        <!-- teachers -->
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class=" d-flex flex-column gap-4 bg-white p-4 shadow rounded-4 mt-4">
                        <div class=" text-secondary">Description (Optional)</div>
                        <form action="" class="d-flex flex-column gap-5 mb-3">
                            <div class="d-flex align-items gap-5">
                                <div class="" style="width: 100%;">
                                    <input class="px-2 py-3" type="text" name="description" id="description" style="width: 100%; border: 1px solid #808b96; border-radius: 5px" placeholder="">
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="d-flex gap-3 align-items-center mt-4">
                        <button class="btn btn-primary fw-semibold d-flex align-items-center" id="editSubjectBtn" style="background-color: #3498db !important; border: none">
                            <div class="loader2 me-2" style="display: none;" id="editSubjectLoader"></div>
                            Save
                        </button>
                        <a href="subjects.php" class="btn btn-secondary fw-semibold text-white">Cancel</a>
                    </div>
                </div>

                <div id="no-internet" class="justify-content-center flex-column align-items-center" style="height: 80%; display: none">
                    <img src="https://hnvs-id-be.creativedevlabs.com/assets/no-connection.png" style="width: 10%;" alt="">
                    <div class="text-secondary fs-6 text-danger">No internet connection</div>
                    <div class="text-secondary" style="font-size: 13px;">Please check your network settings and try again. Some features may not work until you're back online.</div>
                </div>

            </div>
        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <?php include 'partials/_logout.php' ?>
    <?php include 'partials/config.php' ?>
    

    <script>
        const APP_URL = "<?= APP_URL  ?>"

        // prevent backing
        document.addEventListener('DOMContentLoaded', () => {
            const token = localStorage.getItem('token');
            if(!token) {
                location.replace('https://hnvs-id.creativedevlabs.com/');
            }else {
                if (window.history && window.history.pushState) {
                    window.history.pushState(null, null, location.href);
                    window.onpopstate = function () {
                        window.history.pushState(null, null, location.href); // Prevent back
                    };
                }
            }
        });

        window.addEventListener("load", function () {
            setTimeout(() => {
                if(navigator.onLine) {
                    document.getElementById('screenLoaderCon').style.display = 'none';
                    document.getElementById('content').style.display = 'block';
                }else {
                    document.getElementById('screenLoaderCon').style.display = 'none';
                    document.getElementById('no-internet').style.display = 'flex'; 
                }
            }, 800)
        });

        // populate form
        $(document).ready(function () {
            let teacherChoices = null;

            fetch(`${APP_URL}/api/select/teachers`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                }
            })
                .then(res => res.json())
                .then(data => {
                    const choices = data.map(teacher => ({
                        value: teacher.id.toString(),
                        label: `${teacher.firstname} ${teacher.lastname}`
                    }));

                    teacherChoices = new Choices('#teacherSelect', {
                        choices: choices,
                        removeItemButton: true,
                        searchEnabled: true,
                        itemSelectText: '',
                    });

                    const urlParam = new URLSearchParams(window.location.search);
                    const id = urlParam.get('id');

                    fetch(`${APP_URL}/api/find/subject`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + localStorage.getItem('token'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: id })
                    })
                        .then(res => res.json())
                        .then(data => {
                            const subject = data.subject;
                            document.getElementById('subjectName').value = subject.name;
                            document.getElementById('level').value = subject.year_level;
                            document.getElementById('sem').value = subject.semester;
                            if (subject.description) {
                                document.getElementById('description').value = subject.description;
                            }

                            const teachersIds = subject.teachers.map(teacher => teacher.id.toString());

                            // Set selected teachers
                            teachersIds.forEach(id => {
                                teacherChoices.setChoiceByValue(id);
                            });
                        });
                });
        });


        $('#editSubjectBtn').on('click', function(e) {
            e.preventDefault();
            const urlParam = new URLSearchParams(window.location.search);
            const id = urlParam.get('id');

            document.getElementById('editSubjectLoader').style.display = 'block';
            const checkbox = document.getElementById('checkSpecialized');

            const selected = Array.from(document.getElementById('teacherSelect').selectedOptions).map(option => option.value);

            fetch(`${APP_URL}/api/subject/edit`, {
                method: 'POST',
                headers: {
                    'Accept': 'Application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Content-Type': 'Application/json'
                },
                body: JSON.stringify({
                    id: id,
                    name: document.getElementById('subjectName').value,
                    school_year: document.getElementById('sy').value,
                    year_level: document.getElementById('level').value,
                    semester: document.getElementById('sem').value,
                    description: document.getElementById('description').value,
                    teachers: selected,
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.message) {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        color: "#fff",
                        background:  "#28b463",
                        width: 350,
                        toast: true,
                        title: response.message,
                        showConfirmButton: false,
                        timer: 900,
                    })
                    // .then (() => {
                    //     location.href = 'subjects.php';
                    // });
                }else {
                    Swal.fire({
                        position: "top-end",
                        icon: "error",
                        color: "#fff",
                        width: 350,
                        background:  "#cc0202",
                        toast: true,
                        title: response.error,
                        showConfirmButton: false,
                        timer: 4000,
                    })
                }
            })
            .finally(() => {
                document.getElementById('editSubjectLoader').style.display = 'none';
            })

        });

        
    </script>    
