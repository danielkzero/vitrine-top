import { ref } from "vue"
import axios from "axios"

const stored = localStorage.getItem("public_user")
export const publicUser = ref(stored ? JSON.parse(stored) : null)


// ============================================================
// Função auxiliar para testar se a imagem existe
// ============================================================
async function imageExists(url: string | null): Promise<boolean> {
    if (!url) return false

    try {
        const res = await axios.head(url, { validateStatus: () => true })
        return res.status >= 200 && res.status < 300
    } catch {
        return false
    }
}


// ============================================================
// Limpa o cache
// ============================================================
export function clearPublicUser() {
    publicUser.value = null
    localStorage.removeItem("public_user")
}


// ============================================================
// Carregar User Público com verificação de imagens
// ============================================================
export async function loadPublicUser(slug: string) {

    // 1) Se já tem no cache, ainda precisamos validar as imagens
    if (publicUser.value) {

        const user = publicUser.value

        const logoOk = await imageExists(user.logo_path)
        const bgOk = await imageExists(user.background_path)

        // Se qualquer imagem não existir → limpar cache e recarregar
        if (!logoOk || !bgOk) {
            console.warn("⚠️ Imagem inválida detectada, limpando cache…")
            clearPublicUser()
        } else {
            return publicUser.value
        }
    }


    // 2) Buscar User REAL da API
    const response = await axios.get(`/v1/users/${slug}`)
    const user = response.data?.data ?? response.data

    // Validar imagens novamente
    const logoOk = await imageExists(user.logo_path)
    const bgOk = await imageExists(user.background_path)

    // Se imagem não existe → remove caminho inválido
    if (!logoOk) user.logo_path = null
    if (!bgOk) user.background_path = null

    // 3) Salvar usuário no cache
    publicUser.value = user
    localStorage.setItem("public_user", JSON.stringify(user))

    return user
}
