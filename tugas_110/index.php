<?php
session_start();
include 'koneksi.php';

// Handle form 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Add Book
    if (isset($_POST['judul']) && isset($_POST['pengarang']) && isset($_POST['penerbit']) && isset($_POST['tahun_terbit']) && !isset($_POST['id_buku'])) {
        $judul = mysqli_real_escape_string($connection, $_POST['judul']);
        $pengarang = mysqli_real_escape_string($connection, $_POST['pengarang']);
        $penerbit = mysqli_real_escape_string($connection, $_POST['penerbit']);
        $tahun_terbit = mysqli_real_escape_string($connection, $_POST['tahun_terbit']);
        $isbn = mysqli_real_escape_string($connection, $_POST['isbn']);
        $kategori = mysqli_real_escape_string($connection, $_POST['kategori']);
        $jumlah = mysqli_real_escape_string($connection, $_POST['jumlah']);
        
        $query = "INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, isbn, kategori, jumlah) 
                  VALUES ('$judul', '$pengarang', '$penerbit', '$tahun_terbit', '$isbn', '$kategori', '$jumlah')";
        
        if (mysqli_query($connection, $query)) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Buku berhasil ditambahkan!'
            ];
        } else {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Gagal menambahkan buku: ' . mysqli_error($connection)
            ];
        }
    }
    
    // Handle Update Book
    if (isset($_POST['id_buku'])) {
        $id_buku = mysqli_real_escape_string($connection, $_POST['id_buku']);
        $judul = mysqli_real_escape_string($connection, $_POST['judul']);
        $pengarang = mysqli_real_escape_string($connection, $_POST['pengarang']);
        $penerbit = mysqli_real_escape_string($connection, $_POST['penerbit']);
        $tahun_terbit = mysqli_real_escape_string($connection, $_POST['tahun_terbit']);
        $isbn = mysqli_real_escape_string($connection, $_POST['isbn']);
        $kategori = mysqli_real_escape_string($connection, $_POST['kategori']);
        $jumlah = mysqli_real_escape_string($connection, $_POST['jumlah']);
        
        $query = "UPDATE buku SET 
                  judul='$judul', 
                  pengarang='$pengarang', 
                  penerbit='$penerbit', 
                  tahun_terbit='$tahun_terbit', 
                  isbn='$isbn', 
                  kategori='$kategori', 
                  jumlah='$jumlah' 
                  WHERE id_buku='$id_buku'";
        
        if (mysqli_query($connection, $query)) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Buku berhasil diperbarui!'
            ];
        } else {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Gagal memperbarui buku: ' . mysqli_error($connection)
            ];
        }
    }
    
    // Handle Delete Book
    if (isset($_POST['delete_id'])) {
        $id_buku = mysqli_real_escape_string($connection, $_POST['delete_id']);
        
        $query = "DELETE FROM buku WHERE id_buku='$id_buku'";
        
        if (mysqli_query($connection, $query)) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Buku berhasil dihapus!'
            ];
        } else {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Gagal menghapus buku: ' . mysqli_error($connection)
            ];
        }
    }
    
    // Handle Search
    if (isset($_POST['keyword'])) {
        $_SESSION['search_keyword'] = mysqli_real_escape_string($connection, $_POST['keyword']);
    }
    
    header('Location: '.$_SERVER['PHP_SELF']);
    exit();
}

// Clear search 
if (isset($_GET['clear_search'])) {
    unset($_SESSION['search_keyword']);
    header('Location: '.$_SERVER['PHP_SELF']);
    exit();
}

//optional search
$searchQuery = "";
if (isset($_SESSION['search_keyword']) && !empty($_SESSION['search_keyword'])) {
    $keyword = $_SESSION['search_keyword'];
    $searchQuery = " WHERE judul LIKE '%$keyword%' OR pengarang LIKE '%$keyword%' OR isbn LIKE '%$keyword%'";
}

$query = "SELECT * FROM buku" . $searchQuery . " ORDER BY id_buku DESC";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Buku Perpustakaan</title>
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="h-full">
    <div class="container">
        <header class="header">
            <h1>
                <i class="fas fa-book"></i> Manajemen Buku Perpustakaan
            </h1>
            <div class="text-muted">
                <?php echo date('l, F j, Y'); ?>
            </div>
        </header>

        <nav class="nav-tabs">
            <button class="tab-btn active" data-tab="books">
                <i class="fas fa-book-open"></i> Daftar Buku
            </button>
            <button class="tab-btn" data-tab="add">
                <i class="fas fa-plus"></i> Tambah Buku
            </button>
            <button class="tab-btn" data-tab="manage">
                <i class="fas fa-edit"></i> Kelola Buku
            </button>
        </nav>

        <?php if (isset($_SESSION['notification'])): ?>
            <div class="notification <?php echo $_SESSION['notification']['type']; ?> animated mb-4">
                <?php echo $_SESSION['notification']['message']; ?>
                <button class="close-btn" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>

        <div id="books-tab" class="tab-content active">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-semibold">
                    <i class="fas fa-list"></i> Daftar Buku
                </h2>
                <form method="post" class="flex gap-2">
                    <input type="text" name="keyword" placeholder="Cari buku..." class="form-control" 
                           value="<?php echo isset($_SESSION['search_keyword']) ? htmlspecialchars($_SESSION['search_keyword']) : ''; ?>">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if (isset($_SESSION['search_keyword'])): ?>
                        <button type="button" class="btn btn-outline btn-sm" onclick="document.querySelector('input[name=\'keyword\']').value=''; this.form.submit();">
                            <i class="fas fa-times"></i> Reset
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card-grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="card animated">
                        <div class="card-header">
                            <?php echo htmlspecialchars($row['judul']); ?>
                        </div>
                        <div class="card-body">
                            <p class="mb-3"><strong>ID:</strong> <?php echo $row['id_buku']; ?></p>
                            <p class="mb-3"><strong>Pengarang:</strong> <?php echo htmlspecialchars($row['pengarang']); ?></p>
                            <p class="mb-3"><strong>Penerbit:</strong> <?php echo htmlspecialchars($row['penerbit']); ?></p>
                            <p class="mb-3"><strong>Tahun Terbit:</strong> <?php echo htmlspecialchars($row['tahun_terbit']); ?></p>
                            <p class="mb-3"><strong>ISBN:</strong> <?php echo htmlspecialchars($row['isbn']); ?></p>
                            <p class="mb-3"><strong>Kategori:</strong> <?php echo htmlspecialchars($row['kategori']); ?></p>
                            <p><strong>Jumlah:</strong> <?php echo htmlspecialchars($row['jumlah']); ?></p>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $row['id_buku']; ?>"
                                    data-judul="<?php echo htmlspecialchars($row['judul']); ?>"
                                    data-pengarang="<?php echo htmlspecialchars($row['pengarang']); ?>"
                                    data-penerbit="<?php echo htmlspecialchars($row['penerbit']); ?>"
                                    data-tahun_terbit="<?php echo htmlspecialchars($row['tahun_terbit']); ?>"
                                    data-isbn="<?php echo htmlspecialchars($row['isbn']); ?>"
                                    data-kategori="<?php echo htmlspecialchars($row['kategori']); ?>"
                                    data-jumlah="<?php echo htmlspecialchars($row['jumlah']); ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id_buku']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-8">
                        <i class="fas fa-book-open text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Tidak ada data buku ditemukan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="add-tab" class="tab-content">
            <div class="form-section animated">
                <h2 class="mb-4">
                    <i class="fas fa-book-medical"></i> Tambah Buku Baru
                </h2>
                <form method="post" class="form-grid">
                    <div class="form-group">
                        <label for="judul">Judul Buku</label>
                        <input type="text" id="judul" name="judul" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pengarang">Pengarang</label>
                        <input type="text" id="pengarang" name="pengarang" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="penerbit">Penerbit</label>
                        <input type="text" id="penerbit" name="penerbit" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tahun_terbit">Tahun Terbit</label>
                        <input type="number" id="tahun_terbit" name="tahun_terbit" class="form-control" min="1900" max="<?php echo date('Y'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Fiksi">Fiksi</option>
                            <option value="Non-Fiksi">Non-Fiksi</option>
                            <option value="Pendidikan">Pendidikan</option>
                            <option value="Teknologi">Teknologi</option>
                            <option value="Sains">Sains</option>
                            <option value="Sejarah">Sejarah</option>
                            <option value="Agama">Agama</option>
                            <option value="Bisnis">Bisnis</option>
                            <option value="Kesehatan">Kesehatan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah">Jumlah</label>
                        <input type="number" id="jumlah" name="jumlah" class="form-control" min="1" required>
                    </div>
                    
                    <div class="form-group col-span-full">
                        <div class="flex justify-end gap-3">
                            <button type="reset" class="btn btn-outline">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Buku
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="manage-tab" class="tab-content">
            <div class="form-section animated">
                <h2 class="mb-4">
                    <i class="fas fa-edit"></i> Edit Buku
                </h2>
                <form method="post" class="form-grid">
                    <input type="hidden" id="id_buku" name="id_buku">
                    
                    <div class="form-group">
                        <label for="judul_update">Judul Buku</label>
                        <input type="text" id="judul_update" name="judul" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pengarang_update">Pengarang</label>
                        <input type="text" id="pengarang_update" name="pengarang" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="penerbit_update">Penerbit</label>
                        <input type="text" id="penerbit_update" name="penerbit" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tahun_terbit_update">Tahun Terbit</label>
                        <input type="number" id="tahun_terbit_update" name="tahun_terbit" class="form-control" min="1900" max="<?php echo date('Y'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="isbn_update">ISBN</label>
                        <input type="text" id="isbn_update" name="isbn" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori_update">Kategori</label>
                        <select id="kategori_update" name="kategori" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Fiksi">Fiksi</option>
                            <option value="Non-Fiksi">Non-Fiksi</option>
                            <option value="Pendidikan">Pendidikan</option>
                            <option value="Teknologi">Teknologi</option>
                            <option value="Sains">Sains</option>
                            <option value="Sejarah">Sejarah</option>
                            <option value="Agama">Agama</option>
                            <option value="Bisnis">Bisnis</option>
                            <option value="Kesehatan">Kesehatan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="jumlah_update">Jumlah</label>
                        <input type="number" id="jumlah_update" name="jumlah" class="form-control" min="1" required>
                    </div>
                    
                    <div class="form-group col-span-full">
                        <div class="flex justify-end gap-3">
                            <button type="reset" class="btn btn-outline">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sync-alt"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabId = button.getAttribute('data-tab');
                    
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    button.classList.add('active');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });
                        document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const judul = button.getAttribute('data-judul');
                    const pengarang = button.getAttribute('data-pengarang');
                    const penerbit = button.getAttribute('data-penerbit');
                    const tahun_terbit = button.getAttribute('data-tahun_terbit');
                    const isbn = button.getAttribute('data-isbn');
                    const kategori = button.getAttribute('data-kategori');
                    const jumlah = button.getAttribute('data-jumlah');
                    document.getElementById('id_buku').value = id;
                    document.getElementById('judul_update').value = judul;
                    document.getElementById('pengarang_update').value = pengarang;
                    document.getElementById('penerbit_update').value = penerbit;
                    document.getElementById('tahun_terbit_update').value = tahun_terbit;
                    document.getElementById('isbn_update').value = isbn;
                    document.getElementById('kategori_update').value = kategori;
                    document.getElementById('jumlah_update').value = jumlah;
                    
                    document.querySelector('.tab-btn[data-tab="manage"]').click();
                    
                    document.getElementById('manage-tab').scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            document.querySelector('.tab-btn[data-tab="books"]').addEventListener('click', () => {
                if (document.querySelector('input[name="keyword"]').value !== '') {
                    window.location.href = '?clear_search=true';
                }
            });
        });
    </script>
</body>
</html>