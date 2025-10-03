pipeline {
  agent any

  options {
    skipDefaultCheckout(true)
    disableConcurrentBuilds()
    timestamps()
  }

  environment {
    IMAGE_NAME       = 'miguel1212/php-simple-app'
    DOCKER_BUILDKIT  = '1'
    APP_ARCHIVE      = 'php-simple-app.zip'
  }

  stages {
    stage('Checkout') {
      steps {
        git branch: 'main', url: 'https://github.com/miguevillamil1212/php-simple-app.git'
      }
    }

    stage('Detectar Docker en el nodo') {
      steps {
        script {
          def rc = sh(script: 'command -v docker >/dev/null 2>&1', returnStatus: true)
          env.HAS_DOCKER = (rc == 0) ? 'true' : 'false'
          echo "HAS_DOCKER = ${env.HAS_DOCKER}"
        }
      }
    }

    /* ========= Camino A: hay Docker => build & push ========= */
    stage('Build Docker Image') {
      when { expression { env.HAS_DOCKER == 'true' } }
      steps {
        sh '''
          set -euxo pipefail
          docker version
          docker build \
            -t $IMAGE_NAME:latest \
            -t $IMAGE_NAME:${BUILD_NUMBER} .
        '''
      }
    }

    stage('Login & Push a Docker Hub') {
      when { expression { env.HAS_DOCKER == 'true' } }
      steps {
        withCredentials([usernamePassword(
          credentialsId: 'docker-hub-creds',
          usernameVariable: 'DOCKERHUB_USER',
          passwordVariable: 'DOCKERHUB_PASS'
        )]) {
          sh '''
            set -euxo pipefail
            echo "$DOCKERHUB_PASS" | docker login -u "$DOCKERHUB_USER" --password-stdin
            docker push $IMAGE_NAME:latest
            docker push $IMAGE_NAME:${BUILD_NUMBER}
          '''
        }
      }
      post {
        always {
          sh 'docker logout || true'
        }
      }
    }

    stage('Cleanup Docker') {
      when { expression { env.HAS_DOCKER == 'true' } }
      steps {
        sh 'docker system prune -f || true'
      }
    }

    /* ========= Camino B: NO hay Docker => empaquetar y archivar ========= */
    stage('Empaquetar app (sin Docker)') {
      when { expression { env.HAS_DOCKER == "false" } }
      steps {
        sh '''
          set -euxo pipefail
          rm -f "$APP_ARCHIVE"
          # Excluye .git y otros temporales
          zip -r "$APP_ARCHIVE" . -x "*.git*"
        '''
        archiveArtifacts artifacts: "${APP_ARCHIVE}", fingerprint: true
        echo "No hay Docker en el nodo. Se empaquetó la app y se archivó como artefacto: ${APP_ARCHIVE}"
      }
    }
  }

  post {
    success  { echo '✅ Pipeline completado con éxito (Docker push si había Docker; artefacto ZIP si no).' }
    failure  { echo '❌ Pipeline falló' }
  }
}
